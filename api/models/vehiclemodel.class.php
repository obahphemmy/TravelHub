<?php
require_once "model.class.php";

class VehicleModel extends Model {

	function __construct()
	{
		parent::__construct();
	}


	function findVehicles($route_id)
	{
		$sql = "SELECT tr.id trip_id, vt.id vehicle_type_id, num_of_seats, name, fare, amenities, departure_time, company_name, po.park origin_park, pd.park destination_park, travel_id FROM trips tr
				JOIN vehicle_types vt ON tr.vehicle_type_id = vt.id
				JOIN park_map pm ON tr.park_map_id = pm.id
				JOIN parks po ON pm.origin = po.id
				JOIN parks pd ON pm.destination = pd.id
				JOIN travels t ON tr.travel_id = t.id
				WHERE tr.route_id = :route_id AND fare > 0 ";

		self::$db->query($sql, array('route_id' => $route_id));
		return self::$db->fetchAll();
	}


	public function addVehicleType($vehicle_name, $num_of_seat)
	{
		$sql = "INSERT INTO vehicle_types (name, num_of_seats) VALUES (:name, :num_of_seat)";
		$param = array('name' => $vehicle_name, 'num_of_seat' => $num_of_seat);
		if (self::$db->query($sql, $param)) {
			return true;
		}
	}


	public function updateVehicleType($vehicle_name, $num_of_seat, $id)
	{
		$sql = "UPDATE vehicle_types SET
					name = :name,
					num_of_seats = :num_of_seat
				WHERE id = :id";

		$param = array(
			'name' => $vehicle_name,
			'num_of_seat' => $num_of_seat,
			'id' => $id
		);
		if (self::$db->query($sql, $param)) {
			return true;
		}
	}


	public function getAllVehicleTypes()
	{
		$sql = "SELECT * FROM vehicle_types WHERE removed = '0' ORDER BY name";
		self::$db->query($sql);
		return self::$db->fetchAll('obj');
	}


	function charterVehicle()
	{
		$sql = "INSERT INTO vehicle_charter
				(customer_name, customer_phone, next_of_kin, email, departure_location, destination, date_of_travel, date_chartered)
				VALUES
				(:customer_name, :customer_phone, :next_of_kin, :email, :departure_location, :destination, :travel_date, NOW())";

		$param = array(
			'customer_name' => $_POST['customer_name'],
			'customer_phone' => $_POST['customer_phone'],
			'next_of_kin' => $_POST['email'],
			'email' => $_POST['email'],
			'departure_location' => $_POST['departure_location'],
			'destination' => $_POST['destination'],
			'travel_date' => $_POST['travel_date']
		);

		if (self::$db->query($sql, $param))
			return true;
	}


	public function removeVehicle($id)
	{
		$sql = "UPDATE vehicle_types SET removed = '1' WHERE id = :id";
		if (self::$db->query($sql, array('id' => $id))) {

			// remove from fares
			$sql = "DELETE FROM fares WHERE vehicle_type_id = :id";
			self::$db->query($sql, array('id' => $id));
			return true;
		}
	}
}

?>
