?>
<?php
echo [emp_id];
session_start();

try {


    // Handle form submissions
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST['addRow'])) {
            // Redirect to the form page with start_date and end_date as parameters
            $currentWeekStart = isset($_SESSION['currentWeekStartDate']) ? $_SESSION['currentWeekStartDate']->format('Y-m-d') : date('Y-m-d');
            $currentWeekEnd = isset($_SESSION['currentWeekStartDate']) ? (clone $_SESSION['currentWeekStartDate'])->modify('+6 days')->format('Y-m-d') : date('Y-m-d', strtotime('+6 days'));
            sc_redir('form_public_timesheets1', start_date=$currentWeekStart ;end_date=$currentWeekEnd);
            exit; // Ensure script execution stops after redirection
        }

        // Handle navigation buttons
        if (isset($_POST['prevWeek'])) {
            if (isset($_SESSION['currentWeekStartDate']) && $_SESSION['currentWeekStartDate'] instanceof DateTime) {
                $_SESSION['currentWeekStartDate']->modify('-1 week');
            }
        } elseif (isset($_POST['nextWeek'])) {
            if (isset($_SESSION['currentWeekStartDate']) && $_SESSION['currentWeekStartDate'] instanceof DateTime) {
                $_SESSION['currentWeekStartDate']->modify('+1 week');
            }
        } elseif (isset($_POST['today'])) {
            $_SESSION['currentWeekStartDate'] = new DateTime('this week');
        }
    }

    // Fetch data for the logged-in user and current week
    $userId = $_SESSION['emp_id'];
    if (isset($_SESSION['currentWeekStartDate']) && $_SESSION['currentWeekStartDate'] instanceof DateTime) {
        $currentWeekStart = $_SESSION['currentWeekStartDate']->format('Y-m-d');
        $currentWeekEnd = (clone $_SESSION['currentWeekStartDate'])->modify('+6 days')->format('Y-m-d');
    } else {
        $currentWeekStart = date('Y-m-d');
        $currentWeekEnd = date('Y-m-d', strtotime('+6 days'));
    }

    // Query to retrieve work items for the logged-in user and current week
    sc_lookup(workItems, "
        SELECT tm.projectid, tm.work_date, tm.hours_worked, p.projectname
        FROM timesheet_entries tm
        JOIN projects p ON tm.projectid = p.projectid
        WHERE tm.user_id = '$userId'
        AND tm.week_start = '$currentWeekStart'
        AND tm.week_end = '$currentWeekEnd'
    ","Postgres");



} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Week Grid</title>
      <style> 
        body { 
            font-family: Arial, sans-serif; 
            background-color: #f3f9f9; 
            margin: 0; 
            padding: 20px; 
        }
        .week-grid { 
            max-width: 1200px; 
            margin: 0 auto; 
            background-color: #fff; 
            border-radius: 8px; 
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); 
            overflow-x: auto; 
        } 
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 10px; 
        } 
        th, td { 
            padding: 10px; 
            text-align: center; 
            border: 1px solid #ddd; 
        } 
        th { 
            background-color: #f3f9f9; 
            color: #626262; 
            font-weight: bold; 
        } 
         .button-container { 
			display: inline-flex; /* Use flexbox for button alignment */ 
			overflow: hidden; /* Hide overflowing content */ 
			border-radius: 7px 7px 7px 7px; /* Rounded corners */ 
			background-color: #fff; /* Light background color */ 
			box-shadow: 0 2px 6px 0 rgba(227, 234, 239, .5);
			border:1px solid #31B3E6;
			height: 36px;
			margin-top: 20px;
			
		} 
		.button-container button { 
			font-family: Arial, sans-serif; /* Specify font */ 
			font-size: 14px; /* Font size */ 
			font-weight: bold; /* Bold text */ 
			color: #31B3E6; /* Text color */ 
			background-color: #fff; /* Transparent background color */ 
			border: none; /* No border */ 
			padding: 12px 20px; /* Padding around text */ 
			margin: 0; /* Remove default margin */ 
			cursor: pointer; /* Cursor style */ 
			transition: background-color 0.3s ease; /* Smooth transition for hover effect */ 
			outline: none; /* Remove default focus outline */ 
			flex: 1; /* Expand to fill container evenly */ 
			
			
		} 
		.button-container button:not(:last-child) { 
			border-right: 1px solid #31B3E6;; /* Vertical border between buttons */ 
		} 
		.button-container { 
			align-items: right; /* Center-align items vertically within the container */ 
			margin-bottom: 20px;
			margin-left: 10px;
			
		} 
		
        .week-range { 
            text-align: center; 
            font-size: 18px; 
            font-weight: bold; 
            margin-bottom: 20px; 
        }
		.formheader {
			background-color: #31B3E6;
			color: #FFFFFF;
			font-family: Leelawadee, Ebrima, 'Bahnschrift Light', Gadugi, 'Nirmala UI', 'Segoe UI', Verdana;
			font-size: 16px;
			text-decoration: none;
			vertical-align: middle;
			font-weight: bold;
			margin: 0; /* Remove margin */
			border-radius: 8px 8px 0 0; /* Adjust border radius */
			margin: 0; 
			padding: 10px 20px;
		}
        .form-control { 
            width: calc(100% - 24px); 
            height: 34px; 
            padding: 6px 12px; 
            font-size: 16px; 
            color: #555; 
            background-color: #fff; 
            border: 1px solid #ccc; 
            border-radius: 4px; 
            transition: border-color ease-in-out .15s, box-shadow ease-in-out .15s; 
            box-sizing: border-box; 
        } 
        .form-control:focus { 
            border-color: #66afe9; 
            outline: 0; 
            box-shadow: inset 0 1px 1px rgba(0, 0, 0, .075), 0 0 4px rgba(102, 175, 233, .6); 
        } 
		#timesheetForm select,
		  #timesheetForm input[type="text"]{
			width: 40%; /* Make inputs fill the entire width */
			height: 34px;
			padding: 6px 12px;
			font-size: 10px;
			color: #555;
			background-color: #fff;
			border: 1px solid #e7e7e7;
			border-radius: 4px;
			transition: border-color ease-in-out .15s, box-shadow ease-in-out .15s;
			box-sizing: border-box;
			margin-left: 80px;
			box-shadow: none !important;
			box-shadow: 0 2px 6px 0 rgb(227 234 239 / 50%);
			background-image: none;
			opacity: 1;
			filter: alpha(opacity=100);
			border-color: rgba(0,0,0,.05);
			border-style: solid;
			border-width: 1px;
			color: #7c7c7c;
			font-family: 'Roboto', 'Helvetica', 'Arial', sans-serif;
			font-size: 14px;
			text-decoration: none;
			-moz-border-radius: 7px 7px 7px 7px;
			-webkit-border-radius: 7px 7px 7px 7px;
			border-radius: 7px 7px 7px 7px;
			border-bottom: 1px solid #e7e7e7 !important
		}
		 

        @media (max-width: 768px) { 
            table { 
                font-size: 14px; 
            } 
            th, td { 
				
                padding: 8px; 
            } 
			#timesheetForm select,
    #timesheetForm input[type="text"] {
        font-size: 10px;
		font-family: Arial, sans-serif;

    }
        } 
        @media (max-width: 480px) { 
            table { 
                font-size: 12px; 
            } 
            th, td { 
				
				padding: 6px; 
            } 
			#timesheetForm select,#timesheetForm input[type="text"] {
        font-size: 10px;
		font-family: Arial, sans-serif;
    }
        } 
		 button[name="addRow"] { 
           font-family: Leelawadee, Ebrima, 'Bahnschrift Light', Gadugi, 'Nirmala UI', 'Segoe UI', Verdana; 
			color: #31B3E6; 
			font-size: 13px; 
			font-weight: bold; 
			text-decoration: none; 
			border: 1px solid #31B3E6; 
			border-radius: 20px;
			background-color: #fff; 
			padding: 9px 12px; 
			cursor: pointer; 
			transition: all 0.2s; 
			float: right; /* Float the button to the right */ 
			margin-right: 10px; 
			margin-bottom:30px;
			margin-top: 20px;
    }
	#projectId:focus,
#projectRole:focus {
    border-color: #ccc; /* Set border color to a lighter color */
    outline: 0; /* Remove the focus outline */
    box-shadow: none; /* Remove any box shadow */
}


    button[name="save"] { 
       font-family: Leelawadee, Ebrima, 'Bahnschrift Light', Gadugi, 'Nirmala UI', 'Segoe UI', Verdana; 
		color: #31B3E6; 
		font-size: 13px; 
		font-weight: bold; 
		text-decoration: none; 
		border: 1px solid #31B3E6; 
		border-radius: 20px; 
		background-color: #fff; 
		padding: 9px 12px; 
		cursor: pointer; 
		transition: all 0.2s; 
		float: right; /* Float the button to the right */ 
		margin-right: 10px;
		margin-bottom:10px;
    }
	button[name="saveModal"] {
		font-family: Leelawadee, Ebrima, 'Bahnschrift Light', Gadugi, 'Nirmala UI', 'Segoe UI', Verdana; 
		color: #31B3E6; 
		font-size: 13px; 
		font-weight: bold; 
		text-decoration: none; 
		border: 1px solid #31B3E6; 
		border-radius: 20px; 
		background-color: #fff; 
		padding: 9px 12px; 
		cursor: pointer; 
		transition: all 0.2s; 
		float: right; /* Float the button to the right */ 
		margin-right: 10px;
    }
	
	.modal-content button[name="back"] {
    position: absolute; /* Position the button absolutely */
    top: 20px; /* Adjust top distance */
    right: 10px; /* Adjust right distance */
    font-family: Leelawadee, Ebrima, 'Bahnschrift Light', Gadugi, 'Nirmala UI', 'Segoe UI', Verdana;
    color: #31B3E6;
    font-size: 13px;
    font-weight: bold;
    text-decoration: none;
    border: 1px solid #31B3E6;
    border-radius: 20px;
    background-color: #fff;
    padding: 9px 12px;
    cursor: pointer;
    transition: all 0.2s;
	margin-top:40px;
}


	button[name="addRow"]:hover { 
		background-color: #31B3E6; 
		color: #fff; 
} 
	button[name="save"]:hover { 
    background-color: #31B3E6; 
    color: #fff; 
}
		  button[name="saveModal"]:hover { 
    background-color: #31B3E6; 
    color: #fff; 
}
		  button[name="back"]:hover { 
    background-color: #31B3E6; 
    color: #fff; 
} 
   .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5); /* Semi-transparent black overlay */
            z-index: 999; /* Ensure modal is on top of other content */
        }

        /* Style for the modal content */
		.modal-content {
			position: fixed;
			top: 50%;
			left: 50%;
			transform: translate(-50%, -50%);
			background-color: #fff;
			border-radius: 8px;
			box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
			z-index: 1000;
			width: 60%; /* Set the desired width */
			max-width: 800px; /* Set the maximum width if needed */
			height: 70%; /* Set the desired height */
			max-height: 300px; /* Set the maximum height if needed */
			overflow-y: auto; /* Add vertical scrollbar if content exceeds height */
			position: relative; /* Ensure relative positioning for absolute positioning of child elements */
			margin-right: 10px;
		}
		  .GridHeader {
            height: 35px;
            padding: 10px 15px;
            box-sizing: border-box;
            margin: -1px 0px 0px 0px;
            width: 100%;
            background-color: #31B3E6;
            background-image: none;
            opacity: 1;
            filter: alpha(opacity=100);
            border-color: #31B3E6;
            border-style: solid;
            border-width: 1px;
            -moz-border-radius: 7px 7px 0 0;
            -webkit-border-radius: 7px 7px 0 0;
            border-radius: 7px 7px 0 0;
			color: #fff;
			font-weight: bold;
        }
		  h1 {
		  	float: left;
			text-transform: capitalize;
			font-weight: bold;
	}

       #notesSection {
            margin-top: 20px;
            display: none;
		   margin-left:15px;
        }

        #notes {
            width: calc(100% - 24px);
            height: 100px;
            padding: 6px 12px;
            font-size: 16px;
            color: #555;
            background-color: #fff;
            border: 1px solid #ccc;
            border-radius: 4px;
           	transition: border-color ease-in-out .15s, box-shadow ease-in-out .15s;           	
            box-sizing: border-box;
            margin-top: 10px;
        }
		  #notes: focus{
		  	border-color: #ccc; /* Set border color to a lighter color */
			  outline: 0; /* Remove the focus outline */
			  box-shadow: none;
		  }
		  
    </style> 
</head>
<body>
<div class="week-grid">
    <div class="GridHeader">Timesheets</div>
    <div class="container">
        <!-- Form with the "Timesheet" button -->
        <form method="post">
            <button type="submit" name="addRow"><i class="fas fa-plus"></i> Timesheet </button>
        </form>
        <div class="button-container">
            <form method="post">
                <button type="submit" name="prevWeek">&lt;</button>
                <button type="submit" name="today">Current Week</button>
                <button type="submit" name="nextWeek">&gt;</button>
            </form>
        </div>
        <?php
        // Assuming $currentWeekStart and $currentWeekEnd are fetched from the database as strings (Y-m-d format)
        $currentWeekStartObj = new DateTime($currentWeekStart);
        $currentWeekEndObj = new DateTime($currentWeekEnd);
        ?>
        <p class="week-range">
            <?php
            echo $currentWeekStartObj->format('M j');
            echo " - ";
            echo $currentWeekEndObj->format('M j');
            ?>
        </p>
    </div>
    <div style="padding:10px;">
        <table>
            <thead>
                <tr>
                    <th style="width: 20%;">Work Item</th>
                    <?php
                    $currentDate = new DateTime($currentWeekStart);
                    for ($i = 0; $i < 7; $i++) {
                        $weekday = $currentDate->format('D');
                        $dayDate = $currentDate->format('M j');
                        echo "<th style='width: 10%;'>";
                        echo "<div>{$weekday}</div>";
                        echo "<div>{$dayDate}</div>";
                        echo "</th>";
                        $currentDate->modify('+1 day');
                    }
                    ?>
                </tr>
            </thead>
            <tbody>
                <?php
                if (isset($workItems) && is_array($workItems)) {
                    foreach ($workItems as $item) {
                        echo "<tr>";
                        echo "<td>" . $item[3] . "</td>"; // Assuming the project name is in the 4th column
                        $currentDate = new DateTime($currentWeekStart);
                        for ($i = 0; $i < 7; $i++) {
                            $formattedDate = $currentDate->format('Y-m-d');
                            $hoursWorked = '';
                            foreach ($workItems as $ts) {
                                if ($ts[0] == $item[0] && $ts[1] == $formattedDate) {
                                    $hoursWorked = $ts[2];
                                    break;
                                }
                            }
                            echo "<td>
                                <div style='position:relative;'>
                                    <input type='text' name='hours[" . $item[0] . "][" . $formattedDate . "]' value='" . $hoursWorked . "' class='form-control' placeholder='00:00' onclick='clearInput2(this)'>
                                </div>
                              </td>";
                            $currentDate->modify('+1 day');
                        }
                        echo "</tr>";
                    }
                }
                ?>
            </tbody>
        </table>
    </div>
    <!-- Notes Section -->
    <div id="notesSection" style="display: none;">
        <label for="notes">Notes:</label>
        <textarea id="notes" name="notes" rows="10" cols="50" style="border-color: #ccc;outline: 0; box-shadow: none;"></textarea>
    </div>
    <!-- Save Button -->
    <div style="text-align: right; margin-top: 20px;">
        <form method="post">
            <button type="submit" name="save">
                <i class="fas fa-save"></i> Save
            </button>
        </form>
    </div>
</div>
	<script>
		    function clearInput(input) {
        input.value = ''; // Clear the input value when clicked
    }

    function clearInput2(input) {
        input.value = ''; // Clear the input value when clicked
        document.getElementById('notesSection').style.display = 'block'; // Show notes section
    }

    function changeSelectText(select) {
        if (select.value !== '') {
            select.options[0].innerHTML = select.options[select.selectedIndex].text;
        }
    }
</script>
</body>
</html>

<?php
