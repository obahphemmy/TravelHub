<?php
require "includes/head.php";
require "includes/side-bar.php";
require_once "../../includes/db_handle.php";

require_once "../../api/models/user.class.php";
require_once "../../api/models/travelparkmap.class.php";
require_once "../../api/models/parkmodel.class.php";


$travel_park_map = new TravelParkMap();
$park_model = new ParkModel();

$park = $park_model->getParkById($_SESSION['park_id']);

if (isset($_POST['park_map'])) {
    $travel_park_map->addParkMap($_POST['origin'], $_POST['destination_park'], $_SESSION['travel_id']);
}
?>
<style>
.opt-icons .fa { color: #666; font-size: 17px; margin-left: 6px; }
</style>
<!-- Right side column. Contains the navbar and content of the page -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            <i class="fa fa-road"></i> Routes
            <small>Control panel</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Routes</li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
		<div class="row">

			<div class="col-md-6 col-xs-12">
				<div class="box box-danger">
					<div class="box-header with-border">
						<h2 style='font-size: 20px' class="box-title"><i class="fa fa-bus"></i> &nbsp; Routes</h2>
					</div>
					<div class="box-body">
                        <form method="post" id="new_park_map">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <select name="origin" id="origin" class="form-control" required>
                                            <?php
                                            printf("<option value='%d'>%s (%s)</option>", $park->id, $park->state_name, $park->park);
                                            ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <select name="destination" id="destination" class="form-control" required>
                                            <option value="" selected>- Destination (State) -</option>
                                            <?php
                                            $db->query("SELECT * FROM states ORDER BY state_name");
                                            $_states = $db->fetchAll('obj');
                                            foreach ($_states AS $st) {
                                                printf("<option value='%d'>%s</option>", $st->id, $st->state_name);
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <select name="destination_park" id="destination_park" class="form-control" required>
                                            <option value="">-- Destination (Park) --</option>
                                        </select>
                                    </div>
                                </div>
                                <input type="hidden" name="park_map" value="yes" />

                                <div class="col-md-1">
                                    <button type="submit" name="addParkMap" class="btn bg-olive"><i class='fa fa-plus'></i> Add</button>
                                </div>
                            </div>
                        </form>
						<div id="detail-div">
                            <?php
                            $travel_park_maps = $travel_park_map->getTravelParkParkMaps($_SESSION['travel_id'], $_SESSION['park_id']);
                            if (is_array($travel_park_maps) && count($travel_park_maps) > 0):
                            ?>
							<table class="table tablebordered table-striped">
								<thead>
									<tr>
										<th width='30'>S/No</th>
										<th>Origin</th>
										<th>Destination</th>
										<th class='text-center'>Option</th>
									</tr>
								</thead>
								<tbody id="vehicle">
								<?php
									$html = ""; $n = 0;
									foreach ($travel_park_maps as $row) {
										$n++;
										$html .= "<tr>
													<td class='text-right'>$n</td>
													<td>{$row->origin_name}</td>
													<td>{$row->destination_state} ($row->destination_name)</td>
													<td class='opt-icons text-center' id='{$row->id}'>
														<a href='' class='edit-vehicle' title='Edit' data-toggle='tooltip'><i class='fa fa-pencil'></i></a>
														<a href='' class='delete' title='Remove' data-toggle='tooltip'><i class='fa fa-trash-o'></i></a>
													</td>
												</tr>";
									}
									echo $html;
								?>
								</tbody>
							</table>
                            <?php else: ?>
                                <div>
                                    <div class="callout callout-warning">
                                        <p>No route has been created for your State.</p>
                                    </div>
                                </div>
                                <hr />
                            <?php endif; ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>
</div>
<?php
$parks = "";
foreach ($db->query("SELECT * FROM parks WHERE state_id = '". (int)$_SESSION['state_id'] . "' ORDER BY park") AS $st) {
    $parks .= "<option value='{$st['id']}'>{$st['park']}</option>";
}
?>
<!-- Modal -->
<div class="modal fade" id="parkModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Add Park Manager</h4>
            </div>
            <form action="" method="post" id="addPark">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Park</label>
                        <select name="park" class="form-control" required>
                            <option value="">-- Parks --</option>
                            <?php
                            echo $parks;
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Full Name</label>
                        <input class="form-control" type="text" placeholder="Full Name" name="full_name" id="full_name" required>
                    </div>
                    <div class="form-group">
                        <label>Username</label>
                        <input class="form-control" type="text" placeholder="Username" name="username" id="username" required>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input class="form-control" type="password" placeholder="Password" name="password" id="password" required>
                    </div>
                    <div class="form-group">
                        <label>Verify Password</label>
                        <input class="form-control" type="password" placeholder="Verify Password" name="v_password" id="v_password" required>
                    </div>
                    <input type="hidden" name="add_park" value="yes" />
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>

        </div>
    </div>
</div>

<!-- Update Modal -->
<div class="modal fade" id="editPark" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Add Park Manager</h4>
            </div>
            <form action="" method="post" id="update_park">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input class="form-control" type="text" placeholder="Full Name" name="full_name" id="u_full_name" required>
                    </div>
                    <div class="form-group">
                        <label>Username</label>
                        <input class="form-control" type="text" placeholder="Username" name="username" id="u_username" required>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input class="form-control" type="password" placeholder="Password" name="password" id="u_password" required>
                    </div>
                    <div class="form-group">
                        <label>Verify Password</label>
                        <input class="form-control" type="password" placeholder="Verify Password" name="v_password" id="u_v_password" required>
                    </div>
                    <input type="hidden" name="update_park" value="yes" />
                </div>
                <input type="hidden" id="user_id" name="user_id" value="" />
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>

        </div>
    </div>
</div>
<?php include_once "includes/footer.html"; ?>
<script>
$(document).ready(function() {
    $('#addPark').on('submit', function(e) {
        var password = $('#password').val();
        var v_password = $('#v_password').val();

        if (password !== v_password) {
            e.preventDefault();
            alert("Password do not match.");
        }
    });

    $('#destination').on('change', function() {
        var id = $(this).val();
        $.post("../../ajax/misc_fns.php", {"op": "get-state-parks", "state_id": id}, function(d) {
            var parks = JSON.parse(d);
            var _select = $("#destination_park");
            _select.html('<option value="">-- Destination (Park) --</option>');
            $.each(parks, function(i, val) {
                _select.append($('<option />', { value: val.id, text: val.park }));
            });
        });
    });

    $('#new_park_map').on('submit', function(e) {
        if ($('#destination_park').val() == $('#origin').val()) {
            e.preventDefault();
            alert("Destination must be different from origin");
        }
    });

    $('#update_park').on('submit', function(e) {
        var password = $('#u_password').val();
        var v_password = $('#u_v_password').val();

        if (password !== v_password) {
            e.preventDefault();
            alert("Password do not match.");
        }
    });

    $('.edit-park').on('click', function() {
        var $thisTr = $(this).parents('td');
        $('#user_id').val($thisTr.data('userid'));
        $('#u_full_name').val($thisTr.data('fullname'));
        $('#u_username').val($thisTr.data('username'));
    });

});
</script>
