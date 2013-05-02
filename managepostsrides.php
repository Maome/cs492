<?php
	require_once('init.php');
	require_once('ridesharefunctions.php');	
?>
<!DOCTYPE HTML>
<html lang-"en">
    <head>
        <title><?php Woodle(); ?></title>
        <link href="bootstrap/css/bootstrap.css" rel="stylesheet">
        <link href="bootstrap/css/bootstrap-responsive.css" rel="stylesheet">
		<link href="bootstrap/css/datepicker.css" rel="stylesheet">
		<link href="bootstrap/css/bootstrap-modal.css" rel="stylesheet">
		<script type='text/javascript' src='https://ajax.googleapis.com/ajax/libs/jquery/1.7/jquery.min.js'></script>
    </head>
    <body>
		
		<!-- Navbar -->
		<?php DisplayNavbar(basename(__FILE__)); ?>
        
        <div class="container">
            <div class="row-fluid">
				<!-- Sidebar -->
				<?php DisplaySidebar(); ?>
                <div class="span9">
					<?php							
							ManagePostsNav(true, false);
														
							$dbc = new dbw(DBSERVER,DBUSER,DBPASS,DBCATALOG);
							
							// Get the UserID
							$email = phpcas::GetUser() . "@students.wwu.edu";															
							
							// Display all of the current rides that the user has posted 
							$qry = "SELECT UserID FROM User WHERE Email='$email';";
							$result = $dbc->query($qry);
							$row = $result->fetch_assoc();
							$UserID = $row['UserID'];
													
							// Check to see if the user is deleting a post
							if(isset($_POST['delete'])){
								// Update the rideshare if the correct user is trying to remove the rideshare
								$postID = $_POST['pid'];
								if (isset($postID)){
									$qry = "UPDATE RideShare SET RecordStatus='3' WHERE PostID='$postID' AND UserID='$UserID';";
									$dbc->query($qry);
								}								
							}
							
							// Check to see if the user is editing a post
							if(isset($_POST['Edit'])){
								$departureDate = $_POST['departureDate'];									
								$departureHour = $_POST['departureHour'];
								$departureMinute = $_POST['departureMinute'];
								$departureAMPM = $_POST['departureAMPM'];	
								
								$departureLocation = $_POST['departureLocation'];	
														
								$returnDate = $_POST['returnDate'];
								$returnHour = $_POST['returnHour'];
								$returnMinute = $_POST['returnMinute'];			
								$returnAMPM = $_POST['returnAMPM'];	
																	
								$destinationLocation = $_POST['destinationLocation'];		
																					
								$numSeats = $_POST['numSeats'];
								$price = $_POST['price'];
								$postID = $_POST['PostID'];		
								
								$dbc = new dbw(DBSERVER,DBUSER,DBPASS,DBCATALOG);
								
								
								// The data will only get passed if it has been validated client side
								// Convert the departure and return dates to store 
								$departureDate = $departureDate . " " . $departureHour . ":" . $departureMinute . ":00" .  $departureAMPM;
								$returnDate = $returnDate . " " . $returnHour . ":" . $returnMinute . ":00" .  $returnAMPM;
								$departureDate = formatDate($departureDate);
								$returnDate = formatDate($returnDate);					
								
								$source = $_POST['name'];																
							
								// Update the information in the database		
								$sql = "UPDATE RideShare SET RecordStatus='2', DepartureDate='$departureDate', SourceCity='$departureLocation', ReturnDate='$returnDate', DestCity='$destinationLocation', MaxSeats='$numSeats', Price='$price' WHERE PostID='$postID';";
								$dbc->query($sql);								
							}							

							// Populate a table with the rideshares the user currently has posted
							// **** THE SERVER CLOCK IS FAST BY 7 HOURS ****
							$qry = "SELECT * FROM RideShare WHERE RecordStatus != '3' AND UserID='$UserID' AND DepartureDate >= CURRENT_TIMESTAMP - INTERVAL 7 HOUR ORDER BY PostID DESC;";
							$result = $dbc->query($qry);
							$row = $result->fetch_assoc();														

							if ($result->num_rows > 0) {
								$headers = array("Leaving From", "Departing", "Departure Time", "Going To", "Return Date", "Return Time", "Edit");
								echo "<table class='table table-striped'>";
									// Display header
									echo "<thead>";
										foreach ($headers as $i) echo "<th>" .$i ."</th>";
									echo "</thead><tbody>";
									
									$count = 0;
									
									// Display rows
									while($row){			            
									
										// Convert the date/time info
										$departDate = getDateFunc($row['DepartureDate']);
										$departTime = getTime($row['DepartureDate']);
										$returnDate = getDateFunc($row['ReturnDate']);
										$returnTime = getTime($row['ReturnDate']);																				
										
										echo "<tr>
											<td>" . $row['SourceCity'] . "</td>
											<td>" . $departDate . "</td>
											<td>" . $departTime . "</td>
											<td>" . $row['DestCity'] . "</td>
											<td>" . $returnDate . "</td>
											<td>" . $returnTime . "</td>
											<td>
												
												<form class='form-inline' action='managepostsrides.php' method='POST'>
													<input type='hidden' name='pid' id='pid' value='" . $row["PostID"] . "'>
													<input type='hidden' name='delete' id='delete' value='true'>												
													<a href='#edit" . $count . "' role='button' class='btn btn-primary' data-toggle='modal'>Edit</a>
													<input class='btn btn-danger' type='submit' value='Delete' >
												</form>	
											</td>							
										</tr>";		
//										<a href='?delete=true&pid=" . $row["PostID"] . "' role='button' class='btn btn-danger'>Delete</a>

										// Create a modal to edit the rideshare
										include('ridesharemodal.php');
										
										$row = $result->fetch_assoc(); 
										$count++;
									}
									echo "</tbody></table>";									
							}
							else echo "You currently have no rideshares pending";		
							
							// Function to format date information into mysql DATETIME format
							function formatDate($d){									
								$date = date('Y-m-d H:i:s', strtotime($d));									
								return $date;
							}
																															
						?>					
                </div>
            </div>
        </div>
        
    </body>
    <script src="holder/holder.js"></script>
    <script src="http://code.jquery.com/jquery-latest.js"></script>
    <script src="bootstrap/js/bootstrap.js"></script>
	<script src="bootstrap/js/bootstrap-modal.js"></script>
	<script src="bootstrap/js/bootstrap-modalmanager.js"></script>
	<script src="bootstrap/js/bootstrap-datepicker.js"></script>
    <script>$('.datepicker').datepicker();</script>
</html>

