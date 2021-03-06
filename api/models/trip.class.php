<?php

require_once "model.class.php";

class Trip extends Model
{
    
    public function __construct()
    {
        parent::__construct();
    }

    public function addTrip($park_map_id, $departure, $travel_id, $state_id, $route_id, $vehicle_type_id, $amenities, $departure_time, $fare, $channel=null)
    {
        $param = array(
            'park_map_id' => $park_map_id,
            'travel_id' => $travel_id,
            'state_id' => $state_id,
            'departure' => $departure,
            'route_id' => $route_id,
            'vehicle_type_id' => $vehicle_type_id,
            'amenities' => $amenities,
            'departure_time' => $departure_time,
            'fare' => $fare
        );

        $trip_id = $this->verifyTrip($park_map_id, $vehicle_type_id, $departure, $travel_id);
        if (is_numeric($trip_id)) {
            // push this to the concerned terminal
            if ($channel === null) {
                self::pushData($param, 'add-trip');
            }
            return $trip_id;
        }

        $sql = "INSERT INTO trips (park_map_id, travel_id, state_id, departure, vehicle_type_id, route_id, amenities, departure_time, fare)
            VALUES (:park_map_id, :travel_id, :state_id, :departure, :vehicle_type_id, :route_id, :amenities, :departure_time, :fare)";

        self::$db->query($sql, $param);
        // push this to the concerned terminal
        if ($channel === null) {
            self::pushData($param, 'add-trip');
        }
        return self::$db->getLastInsertId();
    }


    public function getTrip($trip_id)
    {
        return self::getOneById('trips', $trip_id);
    }


    /* for ticketing office */
    public function getDailyTrips($vehicle_type_id, $park_map_id, $travel_id)
    {
        $sql = "SELECT id trip_id, fare, park_map_id, travel_id, departure FROM trips
				WHERE vehicle_type_id = :vehicle_type AND park_map_id = :park_map_id AND travel_id = :travel_id";

        $param = array(
            'vehicle_type' => $vehicle_type_id,
            'park_map_id' => $park_map_id,
            'travel_id' => $travel_id
        );

        self::$db->query($sql, $param);
        return self::$db->fetchAll('obj');
    }


    public function updateTrip($trip_id, $amenities, $fare, $departure_order, $departure_time, $channel = null)
    {
        $sql = "UPDATE trips SET
                  amenities = :amenities,
                  fare = :fare,
                  departure = :departure_order,
                  departure_time = :departure_time
                WHERE id = :id";

        $param = array('amenities' => $amenities, 'fare' => $fare, 'id' => $trip_id, 'departure_order' =>$departure_order, 'departure_time' => $departure_time);
        $result = self::$db->query($sql, $param);
        if ($result !== false) {
            if (is_null($channel)) {
                // push to concerned terminal
                self::pushData($param, 'add-trip');
            }
            return true;
        }
        return false;
    }


    public function getTripsByRoute($route_id)
    {
        $sql = "SELECT tr.id trip_id, tp.address, tp.phone, tp.online, vt.id vehicle_type_id, num_of_seats, name, fare, amenities, departure_time, departure, company_name, po.park origin_park, pd.park destination_park, tr.travel_id, tr.park_map_id FROM trips tr
				JOIN vehicle_types vt ON tr.vehicle_type_id = vt.id
				JOIN park_map pm ON tr.park_map_id = pm.id
				JOIN parks po ON pm.origin = po.id
				JOIN parks pd ON pm.destination = pd.id
				JOIN travels t ON tr.travel_id = t.id
				LEFT JOIN travel_park tp ON t.id = tp.travel_id AND po.id = tp.park_id
				WHERE tr.route_id = :route_id AND fare > 0";

        self::$db->query($sql, array('route_id' => $route_id));
        return self::$db->fetchAll();
    }


    public function updateFare($trip_id, $fare)
    {
        $sql = "UPDATE trips SET fare = :fare WHERE id = :id";
        $result = self::$db->query($sql, array('fare' => $fare, 'id' => $trip_id));
        if ($result !== false) {
            return true;
        }
        return false;
    }


    public function getByState($state_id)
    {
        $sql = "SELECT trips.*, po.park AS origin_name, pd.park AS destination_name
                FROM trips 
                JOIN park_map AS pm ON pm.id = trips.park_map_id
                JOIN parks AS po ON po.id = pm.origin
                JOIN parks AS pd ON pd.id = pm.destination
                WHERE trips.state_id = :state_id";
        self::$db->query($sql, array('state_id' => $state_id));
        return self::$db->fetchAll('obj');
    }

    public function getByStateTravel($state_id, $travel_id)
    {
        $sql = "SELECT t.*, po.park AS origin_name, pd.park AS destination_name, vehicle_name
                FROM trips t
                JOIN park_map AS pm ON pm.id = t.park_map_id
                JOIN parks AS po ON po.id = pm.origin
                JOIN parks AS pd ON pd.id = pm.destination
                JOIN vehicle_types vt ON vt.id = t.vehicle_type_id
                JOIN travel_vehicle_types tvt ON vt.id = tvt.vehicle_type_id AND tvt.travel_id = t.travel_id
                WHERE t.state_id = :state_id AND t.travel_id = :travel_id
                ORDER BY vehicle_name";

        try {
            self::$db->query($sql, array('state_id' => $state_id, 'travel_id' => $travel_id));
            return self::$db->fetchAll('obj');
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    public function getByStateTravelParkMap($state_id, $travel_id, $park_map_id)
    {
        $sql = "SELECT trips.*, po.park AS origin_name, pd.park AS destination_name, vt.name AS vehicle_name
                FROM trips
                JOIN park_map AS pm ON pm.id = trips.park_map_id
                JOIN parks AS po ON po.id = pm.origin
                JOIN parks AS pd ON pd.id = pm.destination
                JOIN vehicle_types vt ON vt.id = trips.vehicle_type_id
                WHERE trips.state_id = :state_id AND travel_id = :travel_id AND pm.id = :park_map_id
                ORDER BY vehicle_name";
        self::$db->query($sql, array('state_id' => $state_id, 'travel_id' => $travel_id, 'park_map_id' => $park_map_id));
        return self::$db->fetchAll('obj');
    }

    public function getByParkTravelParkMap($park_id, $travel_id, $park_map_id)
    {
        $sql = "SELECT trips.*, po.park AS origin_name, pd.park AS destination_name, vt.name AS vehicle_name
                FROM trips
                JOIN park_map AS pm ON pm.id = trips.park_map_id
                JOIN parks AS po ON po.id = pm.origin
                JOIN parks AS pd ON pd.id = pm.destination
                JOIN vehicle_types vt ON vt.id = trips.vehicle_type_id
                WHERE po.id = :park_id AND travel_id = :travel_id AND pm.id = :park_map_id
                ORDER BY vehicle_name";
        self::$db->query($sql, array('park_id' => $park_id, 'travel_id' => $travel_id, 'park_map_id' => $park_map_id));
        return self::$db->fetchAll('obj');
    }

    public function getByParkTravel($park_id, $travel_id)
    {
        $sql = "SELECT trips.*, po.park AS origin_name, pd.park AS destination_name, vehicle_name
                FROM trips
                JOIN park_map AS pm ON pm.id = trips.park_map_id
                JOIN parks AS po ON po.id = pm.origin
                JOIN parks AS pd ON pd.id = pm.destination
                JOIN vehicle_types vt ON trips.vehicle_type_id = vt.id
                JOIN travel_vehicle_types tvt ON vt.id = tvt.vehicle_type_id AND tvt.travel_id = trips.travel_id
                WHERE po.id = :park_id AND trips.travel_id = :travel_id
                ORDER BY vehicle_name";
        self::$db->query($sql, array('park_id' => $park_id, 'travel_id' => $travel_id));
        return self::$db->fetchAll('obj');
    }

    private function verifyTrip($park_map_id, $vehicle_type_id, $departure, $travel_id)
    {
        $sql = "SELECT id FROM trips
        WHERE park_map_id = :park_map_id AND vehicle_type_id = :vehicle_type_id AND departure = :departure AND travel_id = :travel_id";
        $param = array(
            'park_map_id' => $park_map_id,
            'vehicle_type_id' => $vehicle_type_id,
            'departure' => $departure,
            'travel_id' => $travel_id
        );
        self::$db->query($sql, $param);
        if ($id = self::$db->fetch('obj')) {
            return $id->id;
        }
    }
}
