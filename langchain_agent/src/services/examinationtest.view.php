<?php

class examinationtest {


	public $core;
	public $view;
	public $item = NULL;

	public $payments;

	public function configView() {
		$this->view->header = TRUE;
		$this->view->footer = TRUE;
		$this->view->menu = TRUE;
		$this->view->javascript = array();
		$this->view->javascript = array('register', 'jquery.form-repeater');
		$this->view->css = array();

		return $this->view;
	}
	private function viewMenu(){
		$userid = $this->core->userID;
		echo '<div class="toolbar">'.
			'<a href="' . $this->core->conf['conf']['path'] . '/lecturer/manage">Create Assessment</a>'.
			'<a href="' . $this->core->conf['conf']['path'] . '/lecturer/courselecturing/'.$userid.'?type=new">Capture Results</a>'.
			'<a href="' . $this->core->conf['conf']['path'] . '/lecturer/courselecturing/'.$userid.'?type=newlecturing">Chairpersons Portal</a>'.
			'<a href="' . $this->core->conf['conf']['path'] . '/lecturer/managelecturing">Academic Board</a>';
		// if($this->core->role == 1000){
		// 	echo '<a href="' . $this->core->conf['conf']['path'] . '/claim/report/Assesment">Report</a>';
		// }
		echo '</div>';
	}

	public function buildView($core) {
		$this->core = $core;
	}

	public function overviewExamination() {

		$this->viewMenu();
		$this->core->helpmanuals('capture_marks_manual.pdf');
		$userid = $this->core->userID;


		//the modal for infor/help.
		echo '<div class="modal fade" id="tutorialModal" tabindex="-1" role="dialog" aria-labelledby="tutorialModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document" style="max-width: 80%; margin: auto;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tutorialModalLabel"> &nbsp;&nbsp;&nbsp;System Tutorial - Understanding Your Assigned Classes Page</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="padding-left: 20px; padding-right: 20px;">
                <h6><i class="fa-solid fa-bars">&nbsp;&nbsp;</i> Navigation Bar</h6>
                <hr>
                <p>At the top of the page, there is the <i>navigation bar</i> . Each button takes you to a separate page:</p>
                <ul>
                    <li><strong>Create Assessment</strong> – Used to create assignments and exams to assess the students.</li>
                    <li><strong>Capture Results</strong> – Whereby after all assessments have been done, the results are recorded and managed here.</li>
                    <li><strong>Chairpersons Portal</strong> – Section reserved for the Chairperson of the department.</li>
                    <li><strong>Academic Board</strong> – Responsible for academic governance, academic standards, and quality of the student experience.</li>
                </ul>
                <h6><i class="fa-solid fa-table"></i> &nbsp;&nbsp;Taught Classes Table</h6>
                <hr>
                <p>After the navigation bar, there is the taught classes table . This table includes classes that have been assigned to lecturers and is divided into sub-headings:</p>
                <ul>
                    <li><strong>#</strong> - Reference number for each class.</li>
                    <li><strong>Code</strong> – The code that a certain course is recognized by.</li>
                    <li><strong>Course</strong> – The course that has been set for the program.</li>
                    <li><strong>Program</strong> – The given program to which courses and course codes are assigned.</li>
                    <li><strong>Year, Part, and Sem</strong> – The year, part, and semester in which the course is being undertaken.</li>
                    <li><strong>Format</strong> – Reflects the type of class, whether Conventional, Parallel, or Block.</li>
                    <li><strong>Campus</strong> – The location where the program is being undertaken.</li>
                    <li><strong>Coursework</strong> – Shows coursework progress, whether it has been created, is incomplete, or is empty.</li>
                    <li><strong>Action</strong> – Divided into <strong>Create CA</strong> (to create assessments) and <strong>Marks</strong> (to record marks from the assessments).</li>
                </ul>
                <h6><i class="fa-solid fa-hand-pointer"></i> &nbsp;&nbsp; Action Explained</h6>
                <hr>
                <p>Action is broken down into two main functions:</p>
                <ol>
                    <li><strong>Create CA (Assessment Creation):</strong> This function is utilized for generating coursework items, specifically assessments. It enables the creation of various assessment types, including assignments, quizzes, and exams, which form an integral part of the coursework.</li>
                    <li><strong>Marks (Recording Marks):</strong> This directs you to a page designed for the input of both examination and coursework marks. It\'s a centralized location where all assessment results are recorded, allowing for efficient management and review of student performance .</li >
                </ol >
                <p ><i> It is crucial to begin with the creation of assessments prior to the recording of examination marks .</i></p >
                <p ><i class="fa-solid fa-info-circle" ></i > <strong > Information Button </strong > – Contains information on how to navigate the page you have opened .</p >
                <p ><i class="fa-solid fa-arrow-rotate-right" ></i > <strong > Refresh Button </strong > – Refreshes the page and updates it immediately if there have been any changes .</p >
            </div >
            <div class="modal-footer" >
                <button type = "button" class="btn btn-primary" data - dismiss = "modal" > Got It!</button >
            </div >
        </div >
    </div >
</div>';



		echo'
		<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card card-accent-primary">
			<div class="card-header">
				<strong><span class="fa-solid fa-users"></span>&nbsp;&nbsp; |&nbsp; &nbsp;Taught Classes </strong>
				<small>Table</small>
			</div>
        <div class="card-body">
		
<div class="alert alert-danger" role="alert" style="border-color: #dc3545; font-size: 12px; font-family: \'Roboto\', sans-serif;">
    <span class="fa-solid fa-info-circle" style="font-size: 11px;"></span>&nbsp; &nbsp;
    If you cannot see your taught classes on the list below, please check with the department chairperson where the course belongs
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
     </button>
</div>';

           
////check whether its SID or ID on basic information
	$lecturer_assigned_sql = "SELECT *,courselecture.ID AS linkID,study.ID As progID, study.ShortName As shortName, courselecture.ID AS assignedID, courselecture.coursecode AS courseID, courses.Name as coursecode,study.Name As prog, SUM(COALESCE(coursework.coursework_weight, 0)) AS total_weight  FROM courselecture  INNER JOIN study ON study.ID = courselecture.classcode INNER JOIN courses ON courses.ID = courselecture.coursecode INNER JOIN `basic-information` ON `basic-information`.SID = `courselecture`.`lecturerECno` INNER JOIN `periods` ON (`periods`.ID = courselecture.periodID)  LEFT JOIN coursework ON courselecture.ID = coursework.lecturer_course_id  WHERE  `basic-information`.ID = '$userid' AND courselecture.status = '1' GROUP BY courselecture.year, courselecture.ID, study.ID, courselecture.coursecode, courses.Name, study.Name ORDER BY courselecture.year DESC, courses.Name, study.Name,part, courselecture.semester;";

// echo $lecturer_assigned_sql ;
// Include necessary Bootstrap styles and scripts
		echo '
<link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.6.2/css/bootstrap.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.6.2/js/bootstrap.bundle.min.js"></script>




<script>
    $(document).on(\'click\', \'.btn-link\', function () {
        const icon = $(this).siblings(\'i\');
        if ($(this).attr(\'aria-expanded\') === \'true\') {
            icon.removeClass(\'fa-chevron-down\').addClass(\'fa-chevron-up\');
        } else {
            icon.removeClass(\'fa-chevron-up\').addClass(\'fa-chevron-down\');
        }
    });
</script>

';



// Query execution
		$lecturer_assigned_classes_run = $this->core->database->doSelectQuery($lecturer_assigned_sql);

// Group data by academic year
		$dataByYear = [];
		while ($row = $lecturer_assigned_classes_run->fetch_assoc()) {
			$year = $row['year'];
			if (!isset($dataByYear[$year])) {
				$dataByYear[$year] = [];
			}
			$dataByYear[$year][] = $row;
		}

// Generate the accordion
		echo '<div class="accordion" id="accordionExample">';

		$accordionCount = 0; // Unique ID for each accordion item
		foreach ($dataByYear as $year => $rows) {
			$accordionCount++;
			$collapseId = "collapseYear" . $accordionCount;
			$headerId = "headingYear" . $accordionCount;


			$tableId  = "tableYear"  . $accordionCount;
			$searchId = "tblSearch"  . $accordionCount;
			$clearId  = "tblClear"   . $accordionCount;


			echo '
    <div class="card">
        <div class="card-header" id="' . $headerId . '" style="font-size: 10px; font-family: \'Arial\', sans-serif; padding: 5px 10px;">
            <button class="btn btn-link accordion-button-custom ' . ($accordionCount === 1 ? '' : 'collapsed') . '" 
				type="button" 
				data-toggle="collapse" 
				data-target="#' . $collapseId . '" 
				aria-expanded="' . ($accordionCount === 1 ? 'true' : 'false') . '" 
				aria-controls="' . $collapseId . '">
				<span>Academic Year ' . $year . '</span>
				<i class="fa ' . ($accordionCount === 1 ? 'fa-chevron-up' : 'fa-chevron-down') . '" aria-hidden="true"></i>
			</button>

        </div>
        <div id="' . $collapseId . '" class="collapse ' . ($accordionCount === 1 ? 'show' : '') . '" aria-labelledby="' . $headerId . '" data-parent="#accordionExample">
            <div class="card-body">







						<!-- one-time icon font include (leave it only in the first loop) -->
<link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
<div style="
        position: relative;
        display: inline-block;
        max-width: 360px;
        float: right;
        padding: 5px 10px;
        font-size: 10px;
        font-family: \'Arial\', sans-serif;
    ">

    <!-- input -->
    <input type="text"
           id="' . $searchId . '"
           placeholder="Search this table…"
           data-target="#' . $tableId . '"
           style="
               width: 100%;
               padding: 6px 30px 6px 30px;   /* text closer to icon */
               border: 1px solid #ccc;
               border-radius: 5px;
               font-size: 10px;
               font-family: \'Arial\', sans-serif;
           "
           onfocus="this.style.borderBottom=\'3px solid #888\';"
           onblur="this.style.borderBottom=\'1px solid #ccc\';"
           onkeyup="filterTable(this)"
    >

    <!-- left search icon -->
    <i class="ri-search-line"
       style="
           position: absolute;
           left: 15px;                /* more padding before icon */
           top: 50%;
           transform: translateY(-50%);
           color: #999;
           font-size: 16px;
           pointer-events: none;
       "></i>

    <!-- right clear icon -->
    <i id="' . $clearId . '"
       class="ri-close-line"
       style="
           position: absolute;
           right: 10px;
           top: 50%;
           transform: translateY(-50%);
           color: #999;
           font-size: 18px;
           cursor: pointer;
           display: none;
       "
       onclick="clearSearch(\'' . $searchId . '\', \'' . $clearId . '\')"></i>


       
</div>

<br>
<br>







			
                <table id="' . $tableId . '"   class="table table-striped table-bordered align-middle" style="font-size: 10px; font-family: \'Arial Narrow\', sans-serif;">
                    <thead class="thead-light">
                        <tr>
                            <th>#</th>
                            <th>Course Code</th>
                            <th>Course Name</th>
                            <th>Programme</th>
                            <th>Regulation Code</th>
                            <th>Year</th>
                            <th>Part</th>
                            <th>Semester</th>
                            <th>Format</th>
                            <th>Intake</th>
                            <th>Campus</th>
                            <th>Coursework</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>';



					echo '
						<!-- jQuery (needed only once) -->
						<script src="https://code.jquery.com/jquery-3.6.0.min.js"
								integrity="sha384-oHn8SAW8g0NplaWMddY10sjT/eMR0aVBW2uYf4FyL1Mm2krz1Y8A8UppYHghbE40"
								crossorigin="anonymous"></script>

						<script>
						/* ========== EVENT WIRING ========== */
						$(function () {
						// 1.  live keyup on every input that has data-target
						$(document).on("keyup", ".table-search", function () {
							filterTable(this);
						});

						// 2.  live click on every clear icon
						$(document).on("click", ".tbl-clear-icon", function () {
							const clearId = this.id;
							const inputId = clearId.replace("tblClear","tblSearch");
							clearSearch(inputId, clearId);
						});
						});

						/* ========== YOUR ORIGINAL LOGIC (safer) ========== */
						function filterTable(input){
							const filter = input.value.toLowerCase();
							const table  = document.querySelector(input.dataset.target);
							if (!table) return;

							const body  = table.tBodies[0];
							if (!body)  return;

							const clear = document.getElementById(
										input.id.replace("tblSearch","tblClear")
										);

							Array.from(body.rows).forEach(row =>
								row.style.display = row.textContent.toLowerCase()
												.includes(filter) ? "" : "none"
							);

							if (clear) clear.style.display = filter ? "block" : "none";
						}

						function clearSearch(inputId, clearId){
							const input = document.getElementById(inputId);
							if (!input) return;

							input.value = "";
							filterTable(input);
							input.focus();

							const clear = document.getElementById(clearId);
							if (clear) clear.style.display = "none";
						}
						</script>';



			$i = 0;
			foreach ($rows as $row) {



				$i++;

				$coursecode  = $row['coursecode'];
				$courseID = $row['courseID'];
				$coursename  = $row['CourseDescription'];
				$part=$row['part'];
				$programme=$row['prog'];
				$shortName=$row['shortName'];
				$regulationCode=$row['regulationCode'];
				$semester=$row['semester'];
				$year =$row['year'];
				$status=$row['status'];
				$campus=$row['campus'];
				$format=$row['format'];
				$classID=$row['assignedID'];
				$progID=$row['progID'];
				$linkID=$row['linkID'];
				$SID=$row['lecturerECno'];
				$total_weight=$row['total_weight'];
				$intake=$row['Intake'];
				$periodID=$row['periodID'];




				echo '
        <tr>
            <td>' . $i . '</td>
            <td>' . $coursecode . '</td>
            <td>' . $coursename . '</td>
            <td>' . $programme . '</td>
            <td>' . $regulationCode . '</td>
            <td>' . $year . '</td>
            <td>' . $part . '</td>
            <td>' . $semester . '</td>
            <td>' . $format . '</td>
            <td>' . $intake . '</td>
            <td>' . $campus . '</td>';

				// Status
				if ($total_weight == 100) {
					echo '<td><span class="badge bg-success">Created</span></td>';
				} elseif ($total_weight == 0) {
					echo '<td><span class="badge bg-danger">Empty</span></td>';
				} else {
					echo '<td><span class="badge bg-warning text-dark">Incomplete</span></td>';
				}

				echo '<td>
   <div class="dropdown">
    <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenuButton' . $index . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="font-size: 10px; font-family: Roboto, sans-serif; color: #007bff; border: none; padding: 3px 8px; line-height: 1;">
        <i class="fas fa-tasks" style="font-size: 10px;"></i> Action
    </button>
    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton' . $index . '">
        <a class="dropdown-item" href="' . $this->core->conf['conf']['path'] . '/assessment/create/' . $linkID . '/" style="font-size: 10px; font-family: Roboto, sans-serif; padding: 2px 8px;">
            <span style="display: inline-block; width: 16px; height: 16px; line-height: 16px; border-radius: 50%; border: 2px solid #007bff; color: #007bff; text-align: center; font-size: 8px;">
                <i class="fas fa-clipboard-list" style="line-height: 14px;"></i>
            </span> 
            Create Assessment
        </a>
        <a class="dropdown-item" href="' . $this->core->conf['conf']['path'] . '/examination/capture?selectedclass=' . $linkID . '&selectedcampus=' . $campus . '&selectedformat=' . $format . '&yearofstudy=' . $part . '&semester=' . $semester . '&year=' . $year . '&selectedRegCode=' . $regulationCode . '&periodID=' . $periodID . '&progID=' . $progID . '&shortName=' . $shortName . '&programme=' . $programme . '" class="load-marks" style="font-size: 10px; font-family: Roboto, sans-serif; padding: 2px 8px;">
            <span style="display: inline-block; width: 16px; height: 16px; line-height: 16px; border-radius: 50%; border: 2px solid #007bff; color: #007bff; text-align: center; font-size: 8px;">
                <i class="fas fa-edit" style="line-height: 14px;"></i>
            </span> 
            Capture Marks
        </a>
    </div>
</div>

</td>';

			}

			echo '
                    </tbody>
                </table>
            </div>
        </div>
    </div>';
		}

		echo '</div>'; // End of accordion

// Close the container
		echo '
                </div>
            </div>
        </div>
    </div>
</div>';

        

		echo '
				<script>
				  
					$(document).ready(function() {
						$(\'.load-marks\').click(function(event) {
							event.preventDefault(); // Prevent the default link behavior
							var url = $(this).attr(\'href\'); // Retrieve the URL from the href attribute of the clicked element
							
							window.location.href = url;
							
							alert(\'Please wait while student marks and details are retrieved and loaded.\'); // Show an alert instead of the modal
							
				
							
				
							
						});
					});
				</script>

';




		echo '


					<!-- Loading Modal -->
			<div class="modal" id="loadingModal" tabindex="-1" role="dialog" aria-labelledby="loadingModalLabel" aria-hidden="true">
				<div class="modal-dialog modal-dialog-centered" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title" id="loadingModalLabel"><i class="fas fa-spinner fa-spin"></i> Loading Data</h5>
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
						</div>
						<div class="modal-body">
							<p>Please wait while student marks and details are retrieved and loaded.</p>
						</div>
					</div>
				</div>
			</div>

';


		echo'</tbody>
	 </table>';



		echo '<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
			<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ho+j7jyWK8fNQe+A12Hb8AhRq26LrZ/JpcUGGOn+Y7RsweNrtN/tE3MoK7ZeZDyx" crossorigin="anonymous"></script>
			<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

			<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
			<script>
			var isEditMode = false;
			var selectedProgrammeID;
			  $(document).ready(function() {});';

	}


	


	//start of examination


	public function captureExaminationtest($selectedclass) {


	public $core;
	public $view;
	public $item = NULL;

	public $payments;

	public function configView() {
		$this->view->header = TRUE;
		$this->view->footer = TRUE;
		$this->view->menu = TRUE;
		$this->view->javascript = array();
		$this->view->javascript = array('register', 'jquery.form-repeater');
		$this->view->css = array();

		return $this->view;
	}
	private function viewMenu(){
		$userid = $this->core->userID;
		echo '<div class="toolbar">'.
			'<a href="' . $this->core->conf['conf']['path'] . '/lecturer/manage">Create Assessment</a>'.
			'<a href="' . $this->core->conf['conf']['path'] . '/lecturer/courselecturing/'.$userid.'?type=new">Capture Results</a>'.
			'<a href="' . $this->core->conf['conf']['path'] . '/lecturer/courselecturing/'.$userid.'?type=newlecturing">Chairpersons Portal</a>'.
			'<a href="' . $this->core->conf['conf']['path'] . '/lecturer/managelecturing">Academic Board</a>';
		// if($this->core->role == 1000){
		// 	echo '<a href="' . $this->core->conf['conf']['path'] . '/claim/report/Assesment">Report</a>';
		// }
		echo '</div>';
	}

	public function buildView($core) {
		$this->core = $core;
	}

	public function overviewExaminationtest() {



   


		$this->viewMenu();
		$this->core->helpmanuals('capture_marks_manual.pdf');
		$userid = $this->core->userID;


		//the modal for infor/help.
		echo '<div class="modal fade" id="tutorialModal" tabindex="-1" role="dialog" aria-labelledby="tutorialModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document" style="max-width: 80%; margin: auto;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tutorialModalLabel"> &nbsp;&nbsp;&nbsp;System Tutorial - Understanding Your Assigned Classes Page</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="padding-left: 20px; padding-right: 20px;">
                <h6><i class="fa-solid fa-bars">&nbsp;&nbsp;</i> Navigation Bar</h6>
                <hr>
                <p>At the top of the page, there is the <i>navigation bar</i> . Each button takes you to a separate page:</p>
                <ul>
                    <li><strong>Create Assessment</strong> – Used to create assignments and exams to assess the students.</li>
                    <li><strong>Capture Results</strong> – Whereby after all assessments have been done, the results are recorded and managed here.</li>
                    <li><strong>Chairpersons Portal</strong> – Section reserved for the Chairperson of the department.</li>
                    <li><strong>Academic Board</strong> – Responsible for academic governance, academic standards, and quality of the student experience.</li>
                </ul>
                <h6><i class="fa-solid fa-table"></i> &nbsp;&nbsp;Taught Classes Table</h6>
                <hr>
                <p>After the navigation bar, there is the taught classes table . This table includes classes that have been assigned to lecturers and is divided into sub-headings:</p>
                <ul>
                    <li><strong>#</strong> - Reference number for each class.</li>
                    <li><strong>Code</strong> – The code that a certain course is recognized by.</li>
                    <li><strong>Course</strong> – The course that has been set for the program.</li>
                    <li><strong>Program</strong> – The given program to which courses and course codes are assigned.</li>
                    <li><strong>Year, Part, and Sem</strong> – The year, part, and semester in which the course is being undertaken.</li>
                    <li><strong>Format</strong> – Reflects the type of class, whether Conventional, Parallel, or Block.</li>
                    <li><strong>Campus</strong> – The location where the program is being undertaken.</li>
                    <li><strong>Coursework</strong> – Shows coursework progress, whether it has been created, is incomplete, or is empty.</li>
                    <li><strong>Action</strong> – Divided into <strong>Create CA</strong> (to create assessments) and <strong>Marks</strong> (to record marks from the assessments).</li>
                </ul>
                <h6><i class="fa-solid fa-hand-pointer"></i> &nbsp;&nbsp; Action Explained</h6>
                <hr>
                <p>Action is broken down into two main functions:</p>
                <ol>
                    <li><strong>Create CA (Assessment Creation):</strong> This function is utilized for generating coursework items, specifically assessments. It enables the creation of various assessment types, including assignments, quizzes, and exams, which form an integral part of the coursework.</li>
                    <li><strong>Marks (Recording Marks):</strong> This directs you to a page designed for the input of both examination and coursework marks. It\'s a centralized location where all assessment results are recorded, allowing for efficient management and review of student performance .</li >
                </ol >
                <p ><i> It is crucial to begin with the creation of assessments prior to the recording of examination marks .</i></p >
                <p ><i class="fa-solid fa-info-circle" ></i > <strong > Information Button </strong > – Contains information on how to navigate the page you have opened .</p >
                <p ><i class="fa-solid fa-arrow-rotate-right" ></i > <strong > Refresh Button </strong > – Refreshes the page and updates it immediately if there have been any changes .</p >
            </div >
            <div class="modal-footer" >
                <button type = "button" class="btn btn-primary" data - dismiss = "modal" > Got It!</button >
            </div >
        </div >
    </div >
</div>';



		echo'
		<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card card-accent-primary">
			<div class="card-header">
				<strong><span class="fa-solid fa-users"></span>&nbsp;&nbsp; |&nbsp; &nbsp;Taught Classes </strong>
				<small>Table</small>
			</div>
        <div class="card-body">
		
<div class="alert alert-danger" role="alert" style="border-color: #dc3545; font-size: 12px; font-family: \'Roboto\', sans-serif;">
    <span class="fa-solid fa-info-circle" style="font-size: 11px;"></span>&nbsp; &nbsp;
    If you cannot see your taught classes on the list below, please check with the department chairperson where the course belongs
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
     </button>
</div>';

           
////check whether its SID or ID on basic information
	$lecturer_assigned_sql = "SELECT *,courselecture.ID AS linkID,study.ID As progID, study.ShortName As shortName, courselecture.ID AS assignedID, courselecture.coursecode AS courseID, courses.Name as coursecode,study.Name As prog, SUM(COALESCE(coursework.coursework_weight, 0)) AS total_weight  FROM courselecture  INNER JOIN study ON study.ID = courselecture.classcode INNER JOIN courses ON courses.ID = courselecture.coursecode INNER JOIN `basic-information` ON `basic-information`.SID = `courselecture`.`lecturerECno` INNER JOIN `periods` ON (`periods`.ID = courselecture.periodID)  LEFT JOIN coursework ON courselecture.ID = coursework.lecturer_course_id  WHERE  `basic-information`.ID = '$userid' AND courselecture.status = '1' GROUP BY courselecture.year, courselecture.ID, study.ID, courselecture.coursecode, courses.Name, study.Name ORDER BY courselecture.year DESC, courses.Name, study.Name,part, courselecture.semester;";


// Include necessary Bootstrap styles and scripts
		echo '
<link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.6.2/css/bootstrap.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.6.2/js/bootstrap.bundle.min.js"></script>




<script>
    $(document).on(\'click\', \'.btn-link\', function () {
        const icon = $(this).siblings(\'i\');
        if ($(this).attr(\'aria-expanded\') === \'true\') {
            icon.removeClass(\'fa-chevron-down\').addClass(\'fa-chevron-up\');
        } else {
            icon.removeClass(\'fa-chevron-up\').addClass(\'fa-chevron-down\');
        }
    });
</script>

';



// Query execution
		$lecturer_assigned_classes_run = $this->core->database->doSelectQuery($lecturer_assigned_sql);

// Group data by academic year
		$dataByYear = [];
		while ($row = $lecturer_assigned_classes_run->fetch_assoc()) {
			$year = $row['year'];
			if (!isset($dataByYear[$year])) {
				$dataByYear[$year] = [];
			}
			$dataByYear[$year][] = $row;
		}

// Generate the accordion
		echo '<div class="accordion" id="accordionExample">';

		$accordionCount = 0; // Unique ID for each accordion item
		foreach ($dataByYear as $year => $rows) {
			$accordionCount++;
			$collapseId = "collapseYear" . $accordionCount;
			$headerId = "headingYear" . $accordionCount;


			$tableId  = "tableYear"  . $accordionCount;
			$searchId = "tblSearch"  . $accordionCount;
			$clearId  = "tblClear"   . $accordionCount;


			echo '
    <div class="card">
        <div class="card-header" id="' . $headerId . '" style="font-size: 10px; font-family: \'Arial\', sans-serif; padding: 5px 10px;">
            <button class="btn btn-link accordion-button-custom ' . ($accordionCount === 1 ? '' : 'collapsed') . '" 
				type="button" 
				data-toggle="collapse" 
				data-target="#' . $collapseId . '" 
				aria-expanded="' . ($accordionCount === 1 ? 'true' : 'false') . '" 
				aria-controls="' . $collapseId . '">
				<span>Academic Year ' . $year . '</span>
				<i class="fa ' . ($accordionCount === 1 ? 'fa-chevron-up' : 'fa-chevron-down') . '" aria-hidden="true"></i>
			</button>

        </div>
        <div id="' . $collapseId . '" class="collapse ' . ($accordionCount === 1 ? 'show' : '') . '" aria-labelledby="' . $headerId . '" data-parent="#accordionExample">
            <div class="card-body">







						<!-- one-time icon font include (leave it only in the first loop) -->
<link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
<div style="
        position: relative;
        display: inline-block;
        max-width: 360px;
        float: right;
        padding: 5px 10px;
        font-size: 10px;
        font-family: \'Arial\', sans-serif;
    ">

    <!-- input -->
    <input type="text"
           id="' . $searchId . '"
           placeholder="Search this table…"
           data-target="#' . $tableId . '"
           style="
               width: 100%;
               padding: 6px 30px 6px 30px;   /* text closer to icon */
               border: 1px solid #ccc;
               border-radius: 5px;
               font-size: 10px;
               font-family: \'Arial\', sans-serif;
           "
           onfocus="this.style.borderBottom=\'3px solid #888\';"
           onblur="this.style.borderBottom=\'1px solid #ccc\';"
           onkeyup="filterTable(this)"
    >

    <!-- left search icon -->
    <i class="ri-search-line"
       style="
           position: absolute;
           left: 15px;                /* more padding before icon */
           top: 50%;
           transform: translateY(-50%);
           color: #999;
           font-size: 16px;
           pointer-events: none;
       "></i>

    <!-- right clear icon -->
    <i id="' . $clearId . '"
       class="ri-close-line"
       style="
           position: absolute;
           right: 10px;
           top: 50%;
           transform: translateY(-50%);
           color: #999;
           font-size: 18px;
           cursor: pointer;
           display: none;
       "
       onclick="clearSearch(\'' . $searchId . '\', \'' . $clearId . '\')"></i>


       
</div>

<br>
<br>







			
                <table id="' . $tableId . '"   class="table table-striped table-bordered align-middle" style="font-size: 10px; font-family: \'Arial Narrow\', sans-serif;">
                    <thead class="thead-light">
                        <tr>
                            <th>#</th>
                            <th>Course Code</th>
                            <th>Course Name</th>
                            <th>Programme</th>
                            <th>Regulation Code</th>
                            <th>Year</th>
                            <th>Part</th>
                            <th>Semester</th>
                            <th>Format</th>
                            <th>Intake</th>
                            <th>Campus</th>
                            <th>Coursework</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>';



					echo '
						<!-- jQuery (needed only once) -->
						<script src="https://code.jquery.com/jquery-3.6.0.min.js"
								integrity="sha384-oHn8SAW8g0NplaWMddY10sjT/eMR0aVBW2uYf4FyL1Mm2krz1Y8A8UppYHghbE40"
								crossorigin="anonymous"></script>

						<script>
						/* ========== EVENT WIRING ========== */
						$(function () {
						// 1.  live keyup on every input that has data-target
						$(document).on("keyup", ".table-search", function () {
							filterTable(this);
						});

						// 2.  live click on every clear icon
						$(document).on("click", ".tbl-clear-icon", function () {
							const clearId = this.id;
							const inputId = clearId.replace("tblClear","tblSearch");
							clearSearch(inputId, clearId);
						});
						});

						/* ========== YOUR ORIGINAL LOGIC (safer) ========== */
						function filterTable(input){
							const filter = input.value.toLowerCase();
							const table  = document.querySelector(input.dataset.target);
							if (!table) return;

							const body  = table.tBodies[0];
							if (!body)  return;

							const clear = document.getElementById(
										input.id.replace("tblSearch","tblClear")
										);

							Array.from(body.rows).forEach(row =>
								row.style.display = row.textContent.toLowerCase()
												.includes(filter) ? "" : "none"
							);

							if (clear) clear.style.display = filter ? "block" : "none";
						}

						function clearSearch(inputId, clearId){
							const input = document.getElementById(inputId);
							if (!input) return;

							input.value = "";
							filterTable(input);
							input.focus();

							const clear = document.getElementById(clearId);
							if (clear) clear.style.display = "none";
						}
						</script>';



			$i = 0;
			foreach ($rows as $row) {



				$i++;

				$coursecode  = $row['coursecode'];
				$courseID = $row['courseID'];
				$coursename  = $row['CourseDescription'];
				$part=$row['part'];
				$programme=$row['prog'];
				$shortName=$row['shortName'];
				$regulationCode=$row['regulationCode'];
				$semester=$row['semester'];
				$year =$row['year'];
				$status=$row['status'];
				$campus=$row['campus'];
				$format=$row['format'];
				$classID=$row['assignedID'];
				$progID=$row['progID'];
				$linkID=$row['linkID'];
				$SID=$row['lecturerECno'];
				$total_weight=$row['total_weight'];
				$intake=$row['Intake'];
				$periodID=$row['periodID'];




				echo '
        <tr>
            <td>' . $i . '</td>
            <td>' . $coursecode . '</td>
            <td>' . $coursename . '</td>
            <td>' . $programme . '</td>
            <td>' . $regulationCode . '</td>
            <td>' . $year . '</td>
            <td>' . $part . '</td>
            <td>' . $semester . '</td>
            <td>' . $format . '</td>
            <td>' . $intake . '</td>
            <td>' . $campus . '</td>';

				// Status
				if ($total_weight == 100) {
					echo '<td><span class="badge bg-success">Created</span></td>';
				} elseif ($total_weight == 0) {
					echo '<td><span class="badge bg-danger">Empty</span></td>';
				} else {
					echo '<td><span class="badge bg-warning text-dark">Incomplete</span></td>';
				}

				echo '<td>
   <div class="dropdown">
    <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenuButton' . $index . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="font-size: 10px; font-family: Roboto, sans-serif; color: #007bff; border: none; padding: 3px 8px; line-height: 1;">
        <i class="fas fa-tasks" style="font-size: 10px;"></i> Action
    </button>
    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton' . $index . '">
        <a class="dropdown-item" href="' . $this->core->conf['conf']['path'] . '/assessment/create/' . $linkID . '/" style="font-size: 10px; font-family: Roboto, sans-serif; padding: 2px 8px;">
            <span style="display: inline-block; width: 16px; height: 16px; line-height: 16px; border-radius: 50%; border: 2px solid #007bff; color: #007bff; text-align: center; font-size: 8px;">
                <i class="fas fa-clipboard-list" style="line-height: 14px;"></i>
            </span> 
            Create Assessment
        </a>
        <a class="dropdown-item" href="' . $this->core->conf['conf']['path'] . '/examination/capture?selectedclass=' . $linkID . '&selectedcampus=' . $campus . '&selectedformat=' . $format . '&yearofstudy=' . $part . '&semester=' . $semester . '&year=' . $year . '&selectedRegCode=' . $regulationCode . '&periodID=' . $periodID . '&progID=' . $progID . '&shortName=' . $shortName . '&programme=' . $programme . '" class="load-marks" style="font-size: 10px; font-family: Roboto, sans-serif; padding: 2px 8px;">
            <span style="display: inline-block; width: 16px; height: 16px; line-height: 16px; border-radius: 50%; border: 2px solid #007bff; color: #007bff; text-align: center; font-size: 8px;">
                <i class="fas fa-edit" style="line-height: 14px;"></i>
            </span> 
            Capture Marks
        </a>
    </div>
</div>

</td>';

			}

			echo '
                    </tbody>
                </table>
            </div>
        </div>
    </div>';
		}

		echo '</div>'; // End of accordion

// Close the container
		echo '
                </div>
            </div>
        </div>
    </div>
</div>';

        

		echo '
				<script>
				  
					$(document).ready(function() {
						$(\'.load-marks\').click(function(event) {
							event.preventDefault(); // Prevent the default link behavior
							var url = $(this).attr(\'href\'); // Retrieve the URL from the href attribute of the clicked element
							
							window.location.href = url;
							
							alert(\'Please wait while student marks and details are retrieved and loaded.\'); // Show an alert instead of the modal
							
				
							
				
							
						});
					});
				</script>

';




		echo '


					<!-- Loading Modal -->
			<div class="modal" id="loadingModal" tabindex="-1" role="dialog" aria-labelledby="loadingModalLabel" aria-hidden="true">
				<div class="modal-dialog modal-dialog-centered" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title" id="loadingModalLabel"><i class="fas fa-spinner fa-spin"></i> Loading Data</h5>
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
						</div>
						<div class="modal-body">
							<p>Please wait while student marks and details are retrieved and loaded.</p>
						</div>
					</div>
				</div>
			</div>

';


		echo'</tbody>
	 </table>';



		echo '<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
			<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ho+j7jyWK8fNQe+A12Hb8AhRq26LrZ/JpcUGGOn+Y7RsweNrtN/tE3MoK7ZeZDyx" crossorigin="anonymous"></script>
			<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

			<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
			<script>
			var isEditMode = false;
			var selectedProgrammeID;
			  $(document).ready(function() {});';

	}


	


	//start of examination


	public function captureExaminationtest($selectedclass) {







		//$this->viewMenu();
		$userid = $this->core->userID;


		// $this->core->helpmanuals('capture_marks_manual.pdf');
		

		include $this->core->conf['conf']['classPath'] . "showoptions.inc.php";
		$optionBuilder = new optionBuilder($this->core);








		$periodID = '';


		//the variables from the URL
		$selectedcampus = $this->core->cleanGet['selectedcampus'];
		$selectedclass = $this->core->cleanGet["selectedclass"];
		$selectedyearofstudy = $this->core->cleanGet['yearofstudy'];
		$selectedsemester = $this->core->cleanGet['semester'];
		$selectedyear = $this->core->cleanGet['year'];
		$selectedprogID = $this->core->cleanGet['progID'];
		$shortName = $this->core->cleanGet['shortName'];
		$selectedformat = trim($this->core->cleanGet['selectedformat']);
		$periodID = $this->core->cleanGet['periodID'];
		$regulationCode = $this->core->cleanGet['selectedRegCode'];
		$class = $this->core->cleanGet['programme'];



// 		echo '<pre>';
// echo 'selectedcampus: '      . htmlspecialchars($selectedcampus, ENT_QUOTES, 'UTF-8')      . PHP_EOL;
// echo 'selectedclass: '       . htmlspecialchars($selectedclass, ENT_QUOTES, 'UTF-8')       . PHP_EOL;
// echo 'yearofstudy: '         . htmlspecialchars($selectedyearofstudy, ENT_QUOTES, 'UTF-8') . PHP_EOL;
// echo 'semester: '            . htmlspecialchars($selectedsemester, ENT_QUOTES, 'UTF-8')    . PHP_EOL;
// echo 'year: '                . htmlspecialchars($selectedyear, ENT_QUOTES, 'UTF-8')        . PHP_EOL;
// echo 'progID: '              . htmlspecialchars($selectedprogID, ENT_QUOTES, 'UTF-8')      . PHP_EOL;
// echo 'selectedformat: '      . htmlspecialchars($selectedformat, ENT_QUOTES, 'UTF-8')      . PHP_EOL;
// echo 'periodID: '            . htmlspecialchars($periodID, ENT_QUOTES, 'UTF-8')            . PHP_EOL;
// echo 'selectedRegCode: '     . htmlspecialchars($regulationCode, ENT_QUOTES, 'UTF-8')      . PHP_EOL;
// echo '</pre>';


// die();





		$assessment_created_sql = "SELECT *, coursework.ID AS courseID,`study`.Name As prog, courses.Name AS coursename FROM `coursework` inner join `courselecture` on `courselecture`.ID = coursework.lecturer_course_id inner join courses on  courses.ID = `courselecture`.coursecode inner join study on study.ID = `courselecture`.classcode  WHERE courselecture.ID = '$selectedclass' ORDER BY coursework.ID ";


		$assessment_created_run = $this->core->database->doSelectQuery($assessment_created_sql);

		// Initialize an array to store the fetched rows
		$assessmentData = [];
		$totalcourseworkitems = 0;



		while ($row = $assessment_created_run->fetch_assoc()) {
			// Add each row to the array
			$assessmentData[] = $row;
			$totalcourseworkitems += 1;
		}
		//  print_r($assessmentData);



		$assessmentDataJSON = json_encode($assessmentData);
		//print_r($assessmentDataJSON);




		$coursename = $assessmentData[0]['CourseDescription'];
		$coursecode = $assessmentData[0]['coursename'];
		$course_id = $assessmentData[0]['coursecode'];
		$assessmenttittle = $assessmentData[0]['CourseDescription'];
		$description = $assessmentData[0]['coursework_content'];
		$total_mark =  $assessmentData[0]['total_mark'];
		$created_at =  $assessmentData[0]['created_at'];
		$coursework_type =  $assessmentData[0]['coursework_type'];





		$regulationCode = $this->core->cleanGet['selectedRegCode'];
		$lastYearInRegulationCode = intval(substr($regulationCode, -4));


							
		$campus      = $selectedcampus;          
		$part        = $selectedclass;         
		$yearofstudy = $selectedyearofstudy;
		$semester    = $selectedsemester;
		$year        = $selectedyear;


		$progcode    = $shortName;        
		$programme   = $selectedprogID;       

		$academicyear = $selectedyear;    






//HARD CODED
		if ($progcode == 'MSC-EGR') {
			$progcode = 'MEGR';
		}


$classCode = $shortName; 






		//GETTING THE MARKING  SCHEME
		$markingscheme_sql = "SELECT * FROM edurole.markingscheme where programmeCode = '$classCode'";
		$markingscheme_run = $this->core->database->doSelectQuery($markingscheme_sql);
//  echo $markingscheme_sql; die();

		while ($row = $markingscheme_run->fetch_assoc()) {
			// Add each row to the array
			$markingschemeData[] = $row;

		}


		$markingschemeDataJSON = json_encode($markingschemeData);



		//get the examWeight and AssessmentWeight using the new data



		$exam_coursework_weight_sql = "select study.Name, `program-course-link`.ID as program_course_id,`program-course-link`.ProgramID,`program-course-link`.Manditory,`program-course-link`.AssessmentWeight,`program-course-link`.ExamWeight,`program-course-link`.OtherWeight,`program-course-link`.PartWeight,courses.ID as courses_id, courses.Name, courses.CourseDescription, courses.CourseCredit	from `courses`
		 inner join `program-course-link` on (courses.ID = `program-course-link`.CourseID  AND `program-course-link`.Year =  '$yearofstudy' AND `program-course-link`.Semester = '$selectedsemester')
		 inner join 
		 programmes on programmes.ID = `program-course-link`.ProgramID
	 	left join `study-program-link` on programmes.ID = `study-program-link`.ProgramID
	 	left join study on study.ID = `study-program-link`.StudyID
		 where study.ShortName = '$progcode' AND  courses.Name = '$coursecode' AND `programmes`.Year = '$yearofstudy' AND `programmes`.Semester = '$selectedsemester' AND  `program-course-link`.AssessmentWeight <> '' AND `program-course-link`.ExamWeight <> '' AND `programmes`.RegulationCode = '$regulationCode' LIMIT 1;";


	// echo '<p>'.$exam_coursework_weight_sql;
  // exit();



		$examWeight = -1;
		$assessmentWeight = -1;
		$otherExam = -1;
    $CourseCredits = 0;

		$exam_coursework_weight_sql_run = $this->core->database->doSelectQuery($exam_coursework_weight_sql);
		$markingscheme_run = $this->core->database->doSelectQuery($markingscheme_sql);






		if ($exam_coursework_weight_sql_run->num_rows > 0) {

			$rows = $exam_coursework_weight_sql_run->fetch_assoc();
			$examWeight = isset($rows['ExamWeight']) ? $rows['ExamWeight'] : -1;
			$assessmentWeight = isset($rows['AssessmentWeight']) ? $rows['AssessmentWeight'] : -1;
//				$otherExam = isset($rows['OtherWeight']) ? $rows['OtherWeight'] : -1;
			$otherExam = (isset($rows['OtherWeight']) && $rows['OtherWeight'] != 0) ? $rows['OtherWeight'] : -1;
   
      $CourseCredits     = isset($rows['CourseCredit']) ? $rows['CourseCredit'] : 0;

		}

		// echo "Exam Weight: " . $examWeight . "<br>";
		// echo "Assessment Weight: " . $assessmentWeight . "<br>";
		// echo "Other Exam: " . $otherExam . "<br>";

		//   exit();




// Safe escaper (if you don't already have it)
$__e = function ($v, $fallback = '—') {
  $v = (string)($v ?? '');
  return htmlspecialchars($v !== '' ? $v : $fallback, ENT_QUOTES, 'UTF-8');
};

// Build "(CLASSCODE, REGCODE)" suffix for Programme
$__class_code = isset($classCode) ? (string)$classCode : (isset($progcode) ? (string)$progcode : '');
$__reg_code   = isset($regulationCode) ? (string)$regulationCode : '';
$__bits       = [];
if ($__class_code !== '') { $__bits[] = htmlspecialchars($__class_code, ENT_QUOTES, 'UTF-8'); }
if ($__reg_code   !== '') { $__bits[] = htmlspecialchars($__reg_code, ENT_QUOTES, 'UTF-8'); }
$__prog_suffix = $__bits ? ' (' . implode(', ', $__bits) . ')' : '';

// Other prepared values (if not already set above)
$__class      = isset($class) ? (string)$class : '';
$__part_sem   = isset($selectedyearofstudy, $selectedsemester) && $selectedyearofstudy !== '' && $selectedsemester !== ''
  ? trim($selectedyearofstudy) . '.' . trim($selectedsemester)
  : (isset($selectedyearofstudy) ? (string)$selectedyearofstudy : '');
$__acad_year  = isset($selectedyear) ? (string)$selectedyear : '';
$__course_name= isset($coursename) ? (string)$coursename : '';
$__course_code= isset($coursecode) ? (string)$coursecode : '';

echo '
<link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

<div id="marks-manual-modal" role="dialog" aria-modal="true" aria-labelledby="marks-manual-title" style="display:none;">
  <!-- Backdrop -->
  <div id="marks-manual-backdrop"
       onclick="closeManualModal()"
       style="position:fixed;inset:0;background:rgba(0,0,0,.35);z-index:9998;"></div>

  <!-- Dialog -->
  <div style="position:fixed;inset:0;display:flex;align-items:center;justify-content:center;z-index:9999;pointer-events:none;">
    <div role="document"
         style="pointer-events:auto;background:#ffffff;border:1px solid #e5e7eb;border-radius:6px;
                width:min(1300px,98vw);height:96vh;max-height:96vh;display:flex;flex-direction:column;overflow:hidden;
                box-shadow:0 14px 36px rgba(0,0,0,.18);">
      <!-- Header -->
      <div style="display:flex;align-items:center;gap:10px;padding:14px 16px;border-bottom:1px solid #f1f5f9;flex:0 0 auto;">
        <i class="ri-question-line" aria-hidden="true" style="font-size:20px;color:#6b7280;line-height:1;"></i>
        <h3 id="marks-manual-title" style="margin:0;font-size:15px;font-weight:700;color:#4b5563;">Help &amp; Support</h3>
        <div style="margin-left:auto;display:flex;align-items:center;gap:8px;">
          <a href="https://sis.nust.ac.zw/datastore/manuals/capture_marks_manual.pdf" target="_blank" rel="noopener"
             title="Open Manual in new tab"
             style="display:inline-flex;align-items:center;gap:6px;padding:6px 8px;border:1px solid #e5e7eb;background:#fff;color:#4b5563;text-decoration:none;border-radius:6px;">
            <i class="ri-external-link-line" aria-hidden="true" style="font-size:16px;color:#6b7280;"></i>
            <span style="font-size:12px;font-weight:700;">Open Manual</span>
          </a>
          <button type="button" onclick="closeManualModal()" title="Close"
                  style="border:1px solid #e5e7eb;background:#ffffff;color:#6b7280;padding:6px;border-radius:6px;cursor:pointer;">
            <i class="ri-close-line" aria-hidden="true" style="font-size:20px;line-height:1;"></i>
          </button>
        </div>
      </div>

     

<!-- Add/merge this CSS once -->
<style>
  /* Tab bar like the screenshot */
  #help-tabs {
    display:flex; gap:36px; align-items:center;
    padding:0 16px; height:48px;
    border-bottom:1px solid #e5e7eb; background:#fff;
  }
  #help-tabs .help-tab {
    appearance:none; background:transparent; border:0; cursor:pointer;
    margin:0; padding:0; height:100%;
    display:inline-flex; align-items:center; gap:8px;
    font-family: Roboto, system-ui, -apple-system, "Segoe UI", Arial, sans-serif;
    font-weight:700; font-size:15px; line-height:1;
    color:#111827; position:relative;
    -webkit-tap-highlight-color: transparent;
  }
  #help-tabs .help-tab i {
    font-size:18px; color:#111827;
  }
  /* underline (hidden by default) */
  #help-tabs .help-tab::after {
    content:""; position:absolute; left:0; right:0; bottom:-1px;
    height:2px; background:#1e3a8a; /* deep blue underline */
    transform:scaleX(0); transform-origin:left center;
    transition:transform .18s ease;
  }
  /* active tab shows underline */
  #help-tabs .help-tab[aria-selected="true"]::after {
    transform:scaleX(1);
  }
  /* subtle hover only changes text tone a bit (no background) */
  #help-tabs .help-tab:hover { color:#0b1220; }
  #help-tabs .help-tab:focus,
  #help-tabs .help-tab:active { outline:none; box-shadow:none; background:transparent; }
</style>




<!-- Tabs -->
<div id="help-tabs" role="tablist" aria-label="Help tabs">
  <button id="tab-btn-manual" class="help-tab"
          role="tab" aria-selected="true" tabindex="0" data-tab="manual"
          onclick="setHelpTab(\'manual\')">
    <i class="ri-book-2-line" aria-hidden="true"></i>
    <span>Manual</span>
  </button>

  <button id="tab-btn-email" class="help-tab"
          role="tab" aria-selected="false" tabindex="-1" data-tab="email"
          onclick="setHelpTab(\'email\')">
    <i class="ri-mail-line" aria-hidden="true"></i>
    <span>Email ICTS</span>
  </button>

  <button id="tab-btn-chat" class="help-tab"
          role="tab" aria-selected="false" tabindex="-1" data-tab="chat"
          onclick="setHelpTab(\'chat\')">
    <i class="ri-chat-3-line" aria-hidden="true"></i>
    <span>Chat</span>
  </button>

  <button id="tab-btn-faq" class="help-tab"
          role="tab" aria-selected="false" tabindex="-1" data-tab="faq"
          onclick="setHelpTab(\'faq\')">
    <i class="ri-question-answer-line" aria-hidden="true"></i>
    <span>Frequently Asked Questions</span>
  </button>
</div>

      <!-- Body with left grey line -->
      <div style="display:flex;gap:0;flex:1 1 auto;min-height:0;max-height:none;position:relative;z-index:1;">
        <div style="width:4px;background:#e5e7eb;"></div>

        <div style="flex:1;overflow:hidden;position:relative;background:#fff;display:flex;flex-direction:column;min-height:0;">
          <div style="flex:1 1 auto;min-height:0;position:relative;">
            <div id="tab-panel-manual" role="tabpanel" aria-labelledby="tab-btn-manual" style="display:block;position:absolute;inset:0;">
              <iframe
                src="https://sis.nust.ac.zw/datastore/manuals/capture_marks_manual.pdf"
                title="Marks Capture Manual PDF"
                style="width:100%;height:100%;border:none;display:block;"></iframe>
            </div>

            <div id="tab-panel-email" role="tabpanel" aria-labelledby="tab-btn-email" style="display:none;position:absolute;inset:0;overflow:auto;padding:16px;">
              <div style="font-size:13px;color:#4b5563;margin-bottom:10px;">
                <div style="font-weight:700;color:#4b5563;margin-bottom:6px;">Email ICTS</div>
                <p style="margin:0 0 8px;">Describe your issue and include the course, programme, part/semester, and any screenshots if possible.</p>
              </div>
              <div style="display:flex;flex-direction:column;gap:10px;max-width:760px;">
                <input type="text" placeholder="Your Name" style="border:1px solid #e5e7eb;border-radius:8px;padding:10px 12px;font-size:13px;color:#374151;" />
                <input type="email" placeholder="Your Email" style="border:1px solid #e5e7eb;border-radius:8px;padding:10px 12px;font-size:13px;color:#374151;" />
                <input type="text" placeholder="Subject" style="border:1px solid #e5e7eb;border-radius:8px;padding:10px 12px;font-size:13px;color:#374151;" />
                <textarea placeholder="Message" rows="6" style="border:1px solid #e5e7eb;border-radius:8px;padding:10px 12px;font-size:13px;color:#374151;resize:vertical;"></textarea>
                <div style="display:flex;gap:8px;align-items:center;">
                  <a href="mailto:icts@nust.ac.zw?subject=Marks%20Capture%20Support&body=Please%20describe%20your%20issue..."
                     style="display:inline-flex;align-items:center;gap:8px;border:1px solid #e5e7eb;border-radius:8px;background:#f9fafb;color:#4b5563;
                            padding:10px 14px;font-size:13px;font-weight:700;text-decoration:none;cursor:pointer;">
                    <i class="fa-regular fa-paper-plane" style="font-size:15px;"></i> Send via Email Client
                  </a>
                  <span style="font-size:12px;color:#9ca3af;">Opens your default email app</span>
                </div>
              </div>
            </div>

            <div id="tab-panel-chat" role="tabpanel" aria-labelledby="tab-btn-chat" style="display:none;position:absolute;inset:0;overflow:auto;padding:16px;">
              <div style="font-size:13px;color:#4b5563;margin-bottom:10px;">
                <div style="font-weight:700;color:#4b5563;margin-bottom:6px;">Chat</div>
                <p style="margin:0 0 8px;">Start a quick chat with ICTS (placeholder). Replace the link below with your internal chat URL.</p>
              </div>
              <a href="#" onclick="alert(&quot;Configure chat URL&quot;);return false;"
                 style="display:inline-flex;align-items:center;gap:8px;border:1px solid #e5e7eb;border-radius:8px;background:#ffffff;
                        padding:10px 14px;font-size:13px;font-weight:700;color:#4b5563;text-decoration:none;cursor:pointer;">
                <i class="fa-regular fa-comments" style="font-size:15px;"></i> Open ICTS Chat
              </a>
            </div>

            <div id="tab-panel-faq" role="tabpanel" aria-labelledby="tab-btn-faq" style="display:none;position:absolute;inset:0;overflow:auto;padding:16px;">
              <div style="font-size:13px;color:#4b5563;margin-bottom:10px;">
                <div style="font-weight:700;color:#4b5563;margin-bottom:6px;">Frequently Asked Questions</div>
              </div>
              <div style="display:flex;flex-direction:column;gap:10px;max-width:980px;">
                <details style="border:1px solid #e5e7eb;border-radius:8px;padding:10px 12px;">
                  <summary style="cursor:pointer;font-weight:700;color:#4b5563;">How do I capture coursework and exam marks?</summary>
                  <div style="margin-top:8px;font-size:13px;color:#6b7280;">Open the <em>Manual</em> tab and follow the step-by-step guide in the PDF.</div>
                </details>
                <details style="border:1px solid #e5e7eb;border-radius:8px;padding:10px 12px;">
                  <summary style="cursor:pointer;font-weight:700;color:#4b5563;">Why can’t I submit marks?</summary>
                  <div style="margin-top:8px;font-size:13px;color:#6b7280;">Check the academic period, course code, and that all required fields are completed. If the issue persists, email ICTS.</div>
                </details>
                <details style="border:1px solid #e5e7eb;border-radius:8px;padding:10px 12px;">
                  <summary style="cursor:pointer;font-weight:700;color:#4b5563;">Which browsers are supported?</summary>
                  <div style="margin-top:8px;font-size:13px;color:#6b7280;">Use the latest Chrome, Edge, or Firefox for the best experience.</div>
                </details>
              </div>
            </div>
          </div>
        </div>
      </div> <!-- /Body -->
    </div>
  </div>
</div>

<script>







document.addEventListener(\'DOMContentLoaded\', function () {
   $("#sidebarCollapse").click();

   });
				



  function openManualModal(){
    var m = document.getElementById("marks-manual-modal");
    if(m){ m.style.display = "block"; }
    document.addEventListener("keydown", escCloseManual, { once: true });
    setHelpTab("manual");
  }
  function closeManualModal(){
    var m = document.getElementById("marks-manual-modal");
    if(m){ m.style.display = "none"; }
  }
  function escCloseManual(e){
    if(e && e.key === "Escape"){ closeManualModal(); }
  }
  function setHelpTab(tab){
    var tabs = ["manual","email","chat","faq"];
    for(var i=0;i<tabs.length;i++){
      var t = tabs[i];
      var panel = document.getElementById("tab-panel-"+t);
      var btn   = document.getElementById("tab-btn-"+t);
      if(!panel || !btn) continue;

      var active = (t === tab);
      panel.style.display = active ? "block" : "none";
      btn.setAttribute("aria-selected", active ? "true" : "false");
      btn.setAttribute("tabindex", active ? "0" : "-1");

      btn.style.background = "transparent";
      btn.style.border     = "none";
      btn.style.color      = "#6b7280";
      btn.style.boxShadow  = "none";

      if(active){
        btn.style.background = "#f3f4f6";
        btn.style.border     = "1px solid #e5e7eb";
        btn.style.color      = "#374151";
        btn.style.boxShadow  = "0 1px 2px rgba(0,0,0,.06)";
      }
    }
  }
  (function(){
    var bar = document.getElementById("help-tabs");
    if(!bar) return;
    bar.addEventListener("click", function(e){
      var btn = e.target.closest("[data-tab]");
      if(!btn || !bar.contains(btn)) return;
      e.preventDefault();
      setHelpTab(btn.getAttribute("data-tab"));
    });
  })();
</script>
';


echo '
<link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;600;700&display=swap" rel="stylesheet">

<style>
  :root{
    --ink:#111827;
    --line:#e5e7eb;
  }

  .results-header-wrap{font-family:"Roboto",system-ui,-apple-system,"Segoe UI",Arial,sans-serif;color:var(--ink)}
  .results-header{background:#fff;border:1px solid var(--line);border-radius:14px;padding:18px 20px}
  .results-top{display:flex;align-items:center;justify-content:space-between;gap:16px}
  .results-top-left{display:flex;align-items:center;gap:12px}
  .results-top-left i{font-size:20px;color:var(--ink)}
  .results-top-left h5{margin:0;font-size:18px;font-weight:700;letter-spacing:.2px;color:var(--ink)}

  .results-actions .manual-btn{
    display:inline-flex;align-items:center;gap:10px;
    padding:8px 12px;background:#fff;color:var(--ink);
    border:none;border-radius:6px;box-shadow:none;cursor:pointer;
    font-size:12px;font-weight:700;
  }
  .results-actions .manual-btn:hover{background:#f3f4f6}
  .results-actions .manual-btn:focus,
  .results-actions .manual-btn:active{outline:none;box-shadow:none;background:transparent}
  .results-actions .manual-btn i{font-size:16px;color:var(--ink)}
  .results-actions .manual-btn .divider{width:1px;height:16px;background:var(--ink);display:inline-block}

  /* TAB BAR (no click highlight) */
  .results-tabs-wrap{margin:10px 0 0 0}
  .results-tabs{display:flex;gap:28px;align-items:center;border-bottom:1px solid var(--line);padding:0 0 4px 34px}
  .tab-btn{
    position:relative;appearance:none;background:transparent;border:none;cursor:pointer;
    padding:12px 3px 10px;margin:0;font-weight:700;font-size:16px; /* bigger */
    color:var(--ink);letter-spacing:.1px;display:inline-flex;align-items:center;gap:8px;
  }
  .tab-btn i{font-size:18px;color:var(--ink)}
  .tab-btn[aria-selected="true"]::after{
    content:""; position:absolute; left:0; right:0; bottom:-1px;
    height:2px; background:var(--ink); border-radius:1px;
  }
  /* Remove focus/click highlight entirely */
  .tab-btn:focus, .tab-btn:active{outline:none; box-shadow:none; background:transparent}

  .tab-panel{display:none}
  .tab-panel.active{display:block}

  .results-meta{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:22px;padding:12px 0 8px 0; margin-left:34px; margin-right:6px}
  .meta-item{display:flex;gap:10px;align-items:flex-start}
  .meta-item i{font-size:18px;color:var(--ink);line-height:1.2}
  .meta-label{font-size:12px;color:#6b7280;margin-bottom:2px}
  .meta-value{font-size:15px;color:var(--ink);font-weight:700;line-height:1.25}

  .stat-wrap{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px; margin:12px 6px 6px 34px}
  .stat-card{border:1px solid var(--line);border-radius:10px;padding:12px;background:#fff}
  .stat-label{font-size:12px;color:#6b7280}
  .stat-value{font-size:20px;font-weight:800;color:var(--ink)}

  .assess-wrap{border:1px solid var(--line);border-radius:10px;padding:12px;background:#fff; margin:12px 6px 6px 34px}

  @media (max-width:960px){.results-meta,.stat-wrap{grid-template-columns:1fr 1fr}}
  @media (max-width:560px){.results-meta,.stat-wrap{grid-template-columns:1fr}}
</style>

<div class="results-header-wrap">
  <div class="results-header">

    <div class="results-top">
      <div class="results-top-left">
        <i class="ri-graduation-cap-line"></i>
        <h5>Student Assessment Results</h5>
      </div>

      <div class="results-actions" style="display:flex;justify-content:flex-end;border:0;outline:0;box-shadow:none;background:transparent;">
        <button
          type="button"
          id="open-marks-manual"
          title="Open Marks Capture Manual (PDF)"
          onclick="if(typeof openManualModal===\'function\'){openManualModal();}"
          class="manual-btn"
        >
          <i class="ri-question-line" aria-hidden="true"></i>
          <span class="divider" aria-hidden="true"></span>
          <span>Marks Capture Manual</span>
        </button>
      </div>
    </div>

    <!-- TAB BAR -->
    <div class="results-tabs-wrap">
      <div class="results-tabs" role="tablist" aria-label="Results sections" id="results-tabs">
        <button id="tab-overview" class="tab-btn" role="tab" aria-selected="true" aria-controls="panel-overview" tabindex="0" data-tab="overview">
          <i class="ri-layout-grid-line"></i> Overview
        </button>
       
        <button id="tab-assess" class="tab-btn" role="tab" aria-selected="false" aria-controls="panel-assess" tabindex="-1" data-tab="assess">
          <i class="ri-list-check-2"></i> Assessment Details
        </button>
      </div>
    </div>

    <!-- PANELS -->
    <div id="panel-overview" class="tab-panel active" role="tabpanel" aria-labelledby="tab-overview">
      <div class="results-meta">
        <div class="meta-item">
          <i class="ri-building-2-line"></i>
          <div>
            <div class="meta-label">Programme</div>
            <div class="meta-value">'. $__e($__class) . $__prog_suffix .'</div>
          </div>
        </div>

        <div class="meta-item">
          <i class="ri-medal-2-line"></i>
          <div>
            <div class="meta-label">Part / Semester</div>
            <div class="meta-value">'. $__e($__part_sem) .'</div>
          </div>
        </div>

        <div class="meta-item">
          <i class="ri-calendar-event-line"></i>
          <div>
            <div class="meta-label">Academic Year</div>
            <div class="meta-value">'. $__e($__acad_year) .'</div>
          </div>
        </div>

        <div class="meta-item">
          <i class="ri-book-3-line"></i>
          <div>
            <div class="meta-label">Course</div>
            <div class="meta-value">'. $__e($__course_name) .' ('. $__e($__course_code) .')</div>
          </div>
        </div>
      </div>
    </div>

    <div id="panel-class" class="tab-panel" role="tabpanel" aria-labelledby="tab-class">
      <div class="stat-wrap">
        <div class="stat-card"><div class="stat-label">Total Students</div><div class="stat-value" id="stat-total">—</div></div>
        <div class="stat-card"><div class="stat-label">Passed</div><div class="stat-value" id="stat-passed">—</div></div>
        <div class="stat-card"><div class="stat-label">Failed</div><div class="stat-value" id="stat-failed">—</div></div>
        <div class="stat-card"><div class="stat-label">With Carries</div><div class="stat-value" id="stat-carries">—</div></div>
      </div>
    </div>

    <div id="panel-assess" class="tab-panel" role="tabpanel" aria-labelledby="tab-assess">
      <div class="assess-wrap">
        <div style="font-size:13px;color:#6b7280;">Assessment details will appear here (coursework vs exam, weights, etc.).</div>
      </div>
    </div>

  </div>
</div>

<script>
  (function(){
    var tabsOrder = ["overview","class","assess"];
    function setResultsTab(tab){
      for(var i=0;i<tabsOrder.length;i++){
        var t = tabsOrder[i];
        var btn = document.getElementById("tab-" + t);
        var panel = document.getElementById("panel-" + t);
        if(!btn || !panel) continue;
        var active = (t === tab);
        btn.setAttribute("aria-selected", active ? "true" : "false");
        btn.setAttribute("tabindex", active ? "0" : "-1");
        if(active){ try{ btn.focus({preventScroll:true}); }catch(e){} }
        panel.classList.toggle("active", active);
      }
    }

    var bar = document.getElementById("results-tabs");
    if(bar){
      bar.addEventListener("click", function(e){
        var btn = e.target.closest("[data-tab]") || e.target.closest("[role=\'tab\']");
        if(!btn) return;
        e.preventDefault();
        setResultsTab(btn.getAttribute("data-tab"));
      });

      bar.addEventListener("keydown", function(e){
        var order = ["overview","class","assess"];
        var current = order.find(function(t){
          var b = document.getElementById("tab-"+t);
          return b && b.getAttribute("aria-selected")==="true";
        });
        if(!current) return;
        var i = order.indexOf(current);
        if(e.key==="ArrowRight"){ i=(i+1)%order.length; e.preventDefault(); }
        else if(e.key==="ArrowLeft"){ i=(i-1+order.length)%order.length; e.preventDefault(); }
        else { return; }
        setResultsTab(order[i]);
      });
    }
    setResultsTab("overview");
  })();
</script>
       



                <div class="card-body">
				

				<br class = "hiddendiv"   style = "display:none;">
						<br class = "hiddendiv"   style = "display:none;">
						<!-- Success Alert Message -->
					<div class="alert alert-success mt-3" role="alert" style=" font-size: 10px; padding: 6px; display: none;" id="successAlertContainer">
						<span class="fa fa-check-circle" style="font-size: 10px;"></span>&nbsp; &nbsp;
						<span id="successMessageId"></span>
					</div>
					
					<!-- Error Alert Message -->
					<div class="alert alert-danger mt-3" role="alert" style="font-size: 10px; padding: 6px; display: none;" id="alertContainer">
						<span class="fa fa-info-circle" style="font-size: 10px;"></span>&nbsp; &nbsp;
						<span id="customMessageId"></span>
					</div>
                    
<link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">

<style>
  .nav-tabs.clean{
    display:flex;
    gap:28px;
    border-bottom:1px solid #e5e7eb !important;
    background:#fff;
    font-family: Roboto, system-ui, -apple-system, "Segoe UI", Arial, sans-serif;
  }
  .nav-tabs.clean .nav-item{ margin-bottom:0; }

  /* Tab headers (smaller, no click highlight/block) */
  .nav-tabs.clean .nav-link{
    border:0 !important;
    background:transparent !important;
    margin:0 !important;
    padding:8px 0 10px;          
    font-weight:700;
    font-size:14px;             
    color:#111827;               
    position:relative;
    display:inline-flex;
    align-items:center;
    gap:8px;
    -webkit-tap-highlight-color: transparent; /* no grey tap block on mobile */
    box-shadow:none !important;   
    outline:none;                 
  }
  .nav-tabs.clean .nav-link i{
    font-size:16px;              
    color:#111827;
  }
  .nav-tabs.clean .nav-link:hover{
    color:#0b1220;
    text-decoration:none;
    background:transparent;
  }
  .nav-tabs.clean .nav-link:focus{ box-shadow:none; outline:none; }
  .nav-tabs.clean .nav-link.active{ color:#0b1220; }
  .nav-tabs.clean .nav-link.active::after{
    content:"";
    position:absolute;
    left:0; right:0; bottom:-1px;
    height:2px;                   /* slim underline */
    background:#1e3a8a;           /* deep blue */
  }

  /* Panels */
  .tab-content.clean .tab-pane{ display:none; padding:14px 0 0; }
  .tab-content.clean .tab-pane.active{ display:block; }
</style>

<ul class="nav nav-tabs clean" role="tablist" id="marks-tabs-nav" style="border-bottom:1px solid #e5e7eb;">
  <li class="nav-item" role="presentation">
    <a id="marks-tab-capture"
       class="nav-link active"
       data-toggle="tab"
       href="#tab1"
       role="tab"
       aria-selected="true">
      <i class="ri-edit-2-line" aria-hidden="true"></i> Capture Marks
    </a>
  </li>
  <li class="nav-item" role="presentation">
    <a id="marks-tab-carry"
       class="nav-link"
       data-toggle="tab"
       href="#tab2"
       role="tab"
       aria-selected="false">
      <i class="ri-arrow-left-right-line" aria-hidden="true"></i> Carry Students
    </a>
  </li>
  <li class="nav-item" role="presentation">
    <a id="marks-tab-special"
       class="nav-link"
       data-toggle="tab"
       href="#tab3"
       role="tab"
       aria-selected="false">
      <i class="ri-stethoscope-line" aria-hidden="true"></i> Special Examinations
    </a>
  </li>
  <li class="nav-item" role="presentation">
    <a id="marks-tab-supp"
       class="nav-link"
       data-toggle="tab"
       href="#tab4"
       role="tab"
       aria-selected="false">
      <i class="ri-repeat-line" aria-hidden="true"></i> Supplementary Exams
    </a>
  </li>

 <li class="nav-item" role="presentation">
  <a id="marks-tab-performance"
     class="nav-link"
     data-toggle="tab"
     href="#tab-performance"
     role="tab"
     aria-selected="false">
    <i class="ri-pulse-line" aria-hidden="true"></i> Performance
  </a>
</li>


</ul>







			  <!-- Students details Tab Content -->
			  
			  <div class="tab-content" style="border: 1px solid #dee2e6; border-top: none;">
			  
			
			  
			  
				  <div id="tab1" class="tab-pane fade show active">


						<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

						<br> 
						<div id="tab1" class="tab-pane fade show active">
						<div  style="padding: 8px 10px; background-color: #ffffff; border-bottom: 1px solid #dee2e6;">
						<div style="font-family: \'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif; font-size: 13px; color: #333; font-weight: 600;">Class list</div>
						<div style="font-family: \'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif; font-size: 9px; color: #666;">Sorted by surname</div>
								
								
							<div>	
							
							<div style="float:right; display:flex; align-items:center; gap:0;">
  <!-- Recalculate Marks -->
  <button
    id="recompute-grades-button"
    class="recompute-grades-button"
    data-toggle="modal"
    data-target="#requestcourse"
    title="Recalculate Marks"
    onclick="/* call your recompute handler here */"
    onmouseover="this.style.background=\'#f3f4f6\'"
    onmouseout="this.style.background=\'#ffffff\'"
    onfocus="this.style.outline=\'none\'"
    onblur="this.style.outline=\'none\'"
    style="
      display:inline-flex;align-items:center;gap:10px;
      padding:8px 12px;background:#ffffff;color:#000000;
      border:none;border-radius:6px;box-shadow:none;cursor:pointer;
      font-size:12px;font-weight:700;
    "
  >
    <i class="ri-equalizer-line" aria-hidden="true" style="font-size:16px;color:#000000;line-height:1;"></i>
    <span style="color:#000000;">Recalculate Marks</span>
  </button>

  <!-- Pipe -->
  <span aria-hidden="true" style="padding:0 10px;color:#9ca3af;">|</span>

  <!-- Post Coursework Only -->
  <button
    id="postcourseworkbtn"
    data-toggle="modal"
    data-target="#requestcourse"
    title="Post Coursework Only"
    onclick="/* call your postCourseworkOnly() here */"
    onmouseover="this.style.background=\'#f3f4f6\'"
    onmouseout="this.style.background=\'#ffffff\'"
    onfocus="this.style.outline=\'none\'"
    onblur="this.style.outline=\'none\'"
    style="
      display:inline-flex;align-items:center;gap:10px;
      padding:8px 12px;background:#ffffff;color:#000000;
      border:none;border-radius:6px;box-shadow:none;cursor:pointer;
      font-size:12px;font-weight:700;
    "
  >
  
   <i class="ri-check-line" aria-hidden="true" style="font-size:16px;color:#000000;line-height:1;"></i>

    <span style="color:#000000;">Post Course Assessment Only</span>
  </button>

  <!-- Pipe -->
  <span aria-hidden="true" style="padding:0 10px;color:#9ca3af;">|</span>

  <!-- Post Exam & Coursework -->
  <button
    id="postresultsbtn"
    data-toggle="modal"
    data-target="#requestcourse"
    title="Post Exam &amp; Course Assess."
    onclick="/* your existing handler, if any */"
    onmouseover="this.style.background=\'#f3f4f6\'"
    onmouseout="this.style.background=\'#ffffff\'"
    onfocus="this.style.outline=\'none\'"
    onblur="this.style.outline=\'none\'"
    style="
      display:inline-flex;align-items:center;gap:10px;
      padding:8px 12px;background:#ffffff;color:#000000;
      border:none;border-radius:6px;box-shadow:none;cursor:pointer;
      font-size:12px;font-weight:700;
    "
  >
    
  <i class="ri-check-double-line" aria-hidden="true" style="font-size:16px;color:#000000;line-height:1;"></i>
  <span style="color:#000000;">Post Exam &amp; Course Assess.</span>
  </button>

  <!-- Pipe -->
  <span aria-hidden="true" style="padding:0 10px;color:#9ca3af;">|</span>';



  $qs = [
    'selectedclass'   => $selectedclass,         
    'selectedcampus'  => $selectedcampus ?? '',
    'selectedformat'  => $selectedformat ?? '',
    'yearofstudy'     => $selectedyearofstudy ?? '',
    'semester'        => $selectedsemester ?? '',
    'year'            => $academicyear ?? '',
    'progID'          => $selectedprogID ?? '',
    'shortName'       => $prog_short_code ?? '',
    'programme'       => $class ?? '',   
     'periodID'       => $periodID ?? '',         
];

$exportBase = rtrim($this->core->conf['conf']['path'], '/') . '/examination/export';
$exportUrl  = $exportBase . '?' . http_build_query($qs);


  $courselecture_id = $selectedclass;

  $exportLogsUrl =rtrim($this->core->conf['conf']['path'], '/') . '/examination/logs&courselecture_id=' .rawurlencode($courselecture_id);

     echo '
            <div id="page-preloader" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: #ffffff; z-index: 999999; display: flex; flex-direction: column; justify-content: center; align-items: center;">
                <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                    <span class="sr-only">Loading...</span>
                </div>
                <div style="margin-top: 15px; font-family: \'Roboto\', sans-serif; color: #555;">
                    <strong>Loading Taught Classes...</strong><br>
                    <small>Please wait while we retrieve your academic records.</small>
                </div>
            </div>';


  



      echo '
      
      
      
      
      
      
      
      
      <a
          href="'.$exportUrl.'"
          target="_blank"
          rel="noopener"
          class="tab export-pdf-button"
          id="export-pdf-button2"
          title="Export table to PDF"
          onmouseover="this.style.background=\'#f3f4f6\'"
          onmouseout="this.style.background=\'#ffffff\'"
          onfocus="this.style.outline=\'none\'"
          onblur="this.style.outline=\'none\'"
          style="
            display:inline-flex;align-items:center;gap:10px;
            padding:8px 12px;background:#ffffff;color:#000000;
            border:none;border-radius:6px;box-shadow:none;cursor:pointer;
            font-size:12px;font-weight:700;text-decoration:none;
          "
        >
          <i class="ri-file-pdf-2-line" aria-hidden="true" style="font-size:16px;color:#000000;line-height:1;"></i>
          <span style="color:#000000;">Export to PDF</span>
        </a>


        <!-- Pipe -->
<span aria-hidden="true" style="padding:0 10px;color:#9ca3af;">|</span>

<a
  href="'.$exportLogsUrl.'"
  target="_blank"
  rel="noopener"
  class="tab export-pdf-button"
  id="export-auditlog-pdf-button"
  title="Export Assessment Change Log to PDF"
  onmouseover="this.style.background=\'#f3f4f6\'"
  onmouseout="this.style.background=\'#ffffff\'"
  onfocus="this.style.outline=\'none\'"
  onblur="this.style.outline=\'none\'"
  style="
    display:inline-flex;align-items:center;gap:10px;
    padding:8px 12px;background:#ffffff;color:#000000;
    border:none;border-radius:6px;box-shadow:none;cursor:pointer;
    font-size:12px;font-weight:700;text-decoration:none;
  "
>
  <i class="ri-file-pdf-2-line" aria-hidden="true" style="font-size:16px;color:#000000;line-height:1;"></i>
  <span style="color:#000000;">Export Audit Log PDF</span>
</a>

  

</div>

								</div>
								
								<br>
                <br>
                      <div class="table-search"
     style="position:relative; max-width:320px; margin:0; display:inline-block;
            font-family: Roboto, system-ui, -apple-system, \'Segoe UI\', Arial, sans-serif;
            border-bottom:4px solid #000; padding-bottom:6px;">
  <!-- Left search icon -->
  <i class="ri-search-line"
     style="position:absolute; left:10px; top:50%; transform:translateY(-50%);
            color:#000; font-size:14px; pointer-events:none;"></i>

  <!-- Input (no border) -->
  <input
    type="text"
    id="' . $searchId . '"
    placeholder="Search table…"
    data-target="#' . $tableId . '"
    aria-label="Search table"
    onkeyup="filterTable(this)"
    onfocus="this.style.boxShadow=\'inset 0 0 0 2px rgba(0,0,0,.08)\';"
    onblur="this.style.boxShadow=\'none\';"
    style="width:100%; padding:8px 30px 8px 32px;
           border:0; border-radius:6px;
           font-size:12px; color:#000; outline:none;
           background:#fff;
           font-family: Roboto, system-ui, -apple-system, \'Segoe UI\', Arial, sans-serif;
           caret-color:#000;"
  />

  <!-- Right clear button: always visible, black, circled -->
<button
  type="button"
  title="Clear"
  onclick="var wrap=this.closest(\'.table-search\'); if(!wrap){return;} var el=wrap.querySelector(\'input[type=text]\'); if(el){ el.value=\'\'; filterTable(el); }"
  style="position:absolute; right:6px; top:50%; transform:translateY(-50%);
         background:transparent; border:0; padding:0; margin:0; cursor:pointer;
         display:inline-flex; align-items:center; justify-content:center;
         height:20px; width:20px; border-radius:50%;">
  <i class="ri-close-line" aria-hidden="true"
     style="font-size:14px; color:#000; line-height:1;"></i>
</button>

  
</div>
<!-- Advanced Marks Filter Modal -->
<div class="modal fade" id="advancedMarksModal" tabindex="-1" role="dialog" aria-labelledby="advancedMarksLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content" style="font-family:Roboto,system-ui,-apple-system,\'Segoe UI\',Arial,sans-serif;">
      <div class="modal-header">
        <h6 class="modal-title" id="advancedMarksLabel">
          <i class="ri-equalizer-line" style="margin-right:6px;"></i>
          Advanced Marks Filter
        </h6>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body">
        <!-- NAV-style: Field / Filter / Value -->
        <div class="mb-2" style="font-size:12px; color:#6b7280;">
          Choose a field, condition and value, similar to NAV filter lines.
        </div>

        <div class="row align-items-end">
          <!-- Field -->
          <div class="col-md-4 mb-3">
            <label for="adv-field-1" style="font-size:12px; font-weight:600;">Field</label>
            <select id="adv-field-1" class="form-control form-control-sm">
              <option value="regnum">Reg Number</option>
              <option value="name">Name</option>
              <option value="surname">Surname</option>
              <option value="ocw">OCW</option>
              <option value="exam">Exam</option>
              <option value="om">OM%</option>
              <option value="grade">Grade</option>
              <option value="remark">Remark</option>
              <option value="comment">Comment</option>
            </select>
          </div>

          <!-- Operator -->
          <div class="col-md-3 mb-3">
            <label for="adv-operator-1" style="font-size:12px; font-weight:600;">Filter</label>
            <select id="adv-operator-1" class="form-control form-control-sm">
              <!-- Text / numeric friendly operators -->
              <option value="contains">Contains</option>
              <option value="eq">Equals (=)</option>
              <option value="ne">Not equal (&lt;&gt;)</option>
              <option value="starts">Starts with</option>
              <option value="ends">Ends with</option>
              <option value="gt">Greater than (&gt;)</option>
              <option value="gte">Greater or equal (&gt;=)</option>
              <option value="lt">Less than (&lt;)</option>
              <option value="lte">Less or equal (&lt;=)</option>
            </select>
          </div>

          <!-- Value -->
          <div class="col-md-5 mb-3">
            <label for="adv-value-1" style="font-size:12px; font-weight:600;">Value</label>
            <input type="text" id="adv-value-1" class="form-control form-control-sm" placeholder="Type value e.g. 50, PASS, KUMALO">
          </div>
        </div>

        <div style="font-size:11px; color:#6b7280;">
          For numeric fields (OCW, Exam, OM%) the comparison is numeric. For others it is text-based.
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" id="adv-reset-btn" class="btn btn-light btn-sm">
          <i class="ri-refresh-line" style="font-size:14px;"></i> Reset
        </button>
        <button type="button" id="adv-apply-btn" class="btn btn-dark btn-sm">
          <i class="ri-filter-3-line" style="font-size:14px;"></i> Apply Filter
        </button>
        <button type="button" class="btn btn-outline-secondary btn-sm" data-dismiss="modal">
          Close
        </button>
      </div>
    </div>
  </div>
</div>

<script>
  $(document).on(\'click\', \'#advanced-marks-filters\', function () {
    console.log(\'[advanced-filter] open modal\');
    $(\'#advancedMarksModal\').modal(\'show\');
  });

  function getMarksColumnIndexForField(field) {
    var $headerRow = $(\'#marks_capture_table thead tr\').first();
    var idx = -1;

    switch (field) {
      case \'regnum\':
        idx = 1;
        break;
      case \'name\':
        idx = 2;
        break;
      case \'surname\':
        idx = 3;
        break;
      case \'ocw\':
        idx = $headerRow.find(\'th#cw\').index();
        break;
      case \'exam\':
        idx = $headerRow.find(\'th#exam\').index();
        break;
      case \'om\':
        idx = $headerRow.find(\'th#mark\').index();
        break;
      case \'grade\':
        idx = $headerRow.find(\'th#grade\').index();
        break;
      case \'remark\':
        idx = $headerRow.find(\'th#remark\').index();
        break;
      case \'comment\':
        idx = $headerRow.find(\'th#comment\').index();
        break;
    }

    console.log(\'[advanced-filter] field =\', field, \'-> col index =\', idx);
    return idx;
  }

  function evaluateMarksAdvancedCondition(cellText, op, val, field) {
    var rawCell = (cellText || \'\').trim();
    var rawVal = (val || \'\').trim();

    var isNumericField = (field === \'ocw\' || field === \'exam\' || field === \'om\');
    if (isNumericField) {
      var cellNum = parseFloat(rawCell);
      var filterNum = parseFloat(rawVal);

      if (isNaN(cellNum) || isNaN(filterNum)) {
        var c = rawCell.toLowerCase();
        var v = rawVal.toLowerCase();
        if (op === \'contains\') { return c.indexOf(v) !== -1; }
        if (op === \'notcontains\') { return c.indexOf(v) === -1; }
        if (op === \'starts\') { return c.indexOf(v) === 0; }
        if (op === \'ends\') { return c.endsWith(v); }
        if (op === \'eq\') { return c === v; }
        if (op === \'ne\') { return c !== v; }
        return true;
      }

      switch (op) {
        case \'eq\':  return cellNum === filterNum;
        case \'ne\':  return cellNum !== filterNum;
        case \'gt\':  return cellNum > filterNum;
        case \'gte\': return cellNum >= filterNum;
        case \'lt\':  return cellNum < filterNum;
        case \'lte\': return cellNum <= filterNum;
        case \'contains\':    return rawCell.toLowerCase().indexOf(rawVal.toLowerCase()) !== -1;
        case \'notcontains\': return rawCell.toLowerCase().indexOf(rawVal.toLowerCase()) === -1;
        case \'starts\':      return rawCell.toLowerCase().indexOf(rawVal.toLowerCase()) === 0;
        case \'ends\':        return rawCell.toLowerCase().endsWith(rawVal.toLowerCase());
        default:              return true;
      }
    }

    var cellLower = rawCell.toLowerCase();
    var valLower  = rawVal.toLowerCase();

    switch (op) {
      case \'contains\':    return cellLower.indexOf(valLower) !== -1;
      case \'notcontains\': return cellLower.indexOf(valLower) === -1;
      case \'starts\':      return cellLower.indexOf(valLower) === 0;
      case \'ends\':        return cellLower.endsWith(valLower);
      case \'eq\':          return cellLower === valLower;
      case \'ne\':          return cellLower !== valLower;
      case \'gt\':          return cellLower > valLower;
      case \'gte\':         return cellLower >= valLower;
      case \'lt\':          return cellLower < valLower;
      case \'lte\':         return cellLower <= valLower;
      default:              return true;
    }
  }

  function applyAdvancedMarksFilterFromModal() {
    var field = $(\'#adv-field-1\').val();
    var op    = $(\'#adv-operator-1\').val();
    var val   = $(\'#adv-value-1\').val();

    console.log(\'[advanced-filter] apply with:\', { field: field, op: op, val: val });

    var $table = $(\'#marks_capture_table\');
    if (!$table.length) {
      console.log(\'[advanced-filter] ERROR: #marks_capture_table not found\');
      return;
    }

    var colIndex = getMarksColumnIndexForField(field);
    if (colIndex === -1) {
      console.log(\'[advanced-filter] ERROR: column index not found for field\', field);
      return;
    }

    if (!val || val.trim() === \'\') {
      console.log(\'[advanced-filter] empty value -> resetting to normal filter\');
      $(\'#marks-filter\').trigger(\'change\');
      $(\'#advancedMarksModal\').modal(\'hide\');
      return;
    }

    $(\'#marks-filter\').val(\'all\');
    var $rows = $table.find(\'tbody tr\');
    $rows.show();

    $rows.each(function () {
      var $tr = $(this);
      var $tds = $tr.find(\'td\');
      if ($tds.length === 0) { return; }

      var cellText = $tds.eq(colIndex).text();
      var match = evaluateMarksAdvancedCondition(cellText, op, val, field);
      $tr.toggle(!!match);
    });

    $(\'#advancedMarksModal\').modal(\'hide\');
  }

  $(document).on(\'click\', \'#adv-apply-btn\', function (e) {
    e.preventDefault();
    applyAdvancedMarksFilterFromModal();
  });

  $(document).on(\'click\', \'#adv-reset-btn\', function (e) {
    e.preventDefault();
    console.log(\'[advanced-filter] reset\');
    $(\'#adv-value-1\').val(\'\');
    $(\'#marks-filter\').val(\'all\').trigger(\'change\');
    $(\'#advancedMarksModal\').modal(\'hide\');
  });
</script>






<div class="marks-controls staffbar-marks">

      <br>

  <!-- Remix Icon (in your <head> if not already added) -->
<link
  href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css"
  rel="stylesheet"
/>
<style>
  .staffbar-marks{
    display:flex;
    align-items:center;
    gap:26px; /* more spacing between items */
    margin:6px 0 8px;
    font-family:Roboto,system-ui,-apple-system,"Segoe UI",Arial,sans-serif;
    white-space:nowrap;
    color:#000;
  }

  .staffbar-item{
    display:flex;
    align-items:center;
    gap:8px;
    flex:0 0 auto;
  }

  .staffbar-label{
    display:inline-flex;
    align-items:center;
    gap:4px;
    font-size:13.5px;
    font-weight:700;
    margin:0;
    color:#000; /* keep labels solid black */
  }

  .staffbar-label i{
    font-size:16px;
  }

  .staffbar-select-wrap{
    position:relative;
    display:inline-flex;
    align-items:center;
  }

  .staffbar-select{
    appearance:none;
    -webkit-appearance:none;
    -moz-appearance:none;
    border:0;
    padding:3px 20px 3px 2px;
    font-size:13.5px;
    font-weight:700;
    background:transparent;
    color:#6b7280;          /* muted grey for values */
    line-height:1.2;
    cursor:pointer;
  }

  /* Optional: ensure options are also grey in the dropdown list */
  .staffbar-select option{
    color:#4b5563;
  }

  .staffbar-select-icon{
    position:absolute;
    right:3px;
    font-size:16px;
    color:#000;
    pointer-events:none;
  }

  .staffbar-btn{
    border:0;
    background:transparent;
    font-size:13.5px;
    font-weight:700;
    display:inline-flex;
    align-items:center;
    gap:6px;
    cursor:pointer;
    color:#000;
    padding:0 4px;
  }

  .staffbar-btn i{
    font-size:16px;
  }
</style>

<div class="staffbar-marks">

  <!-- Filter -->
  <div class="staffbar-item">
    <span class="staffbar-label">
      <i class="ri-filter-3-line"></i>
      Filter
    </span>
     <div class="staffbar-select-wrap">
      <select id="marks-filter" class="staffbar-select">
        <option value="all" selected>All</option>
        <option value="passed">Passed Only</option>
        <option value="failed">Failed</option>
        <option value="distinctions">Distinctions</option>
        <option value="males">Males</option>
        <option value="females">Females</option>
        <option value="no-marks">No Marks</option>
        <option value="captured">Captured</option>
      </select>
      <i class="ri-arrow-down-s-line staffbar-select-icon"></i>
    </div>
  </div>

  <!-- Sort By -->
  <div class="staffbar-item">
    <span class="staffbar-label">
      <i class="ri-sort-asc"></i>
      Sort By
    </span>
    <div class="staffbar-select-wrap">
      <select id="marks-sortby" class="staffbar-select">
        <option value="name" >Name</option>
        <option value="surname" selected>Surname</option>
        <option value="ocw">OCW</option>
        <option value="om">OM</option>
        <option value="remark">Remark</option>
      </select>
      <i class="ri-arrow-down-s-line staffbar-select-icon"></i>
    </div>
  </div>

  <!-- Criteria -->
  <div class="staffbar-item">
    <span class="staffbar-label">
      <i class="ri-arrow-up-down-line"></i>
      Criteria
    </span>
    <div class="staffbar-select-wrap">
      <select id="marks-sortdir" class="staffbar-select">
        <option value="asc" selected >Asc</option>
        <option value="desc" >Desc</option>
      </select>
      <i class="ri-arrow-down-s-line staffbar-select-icon"></i>
    </div>
  </div>

  <!-- Advanced Filter -->
  <button type="button" id="advanced-marks-filters" class="staffbar-btn">
    <i class="ri-equalizer-line"></i>
    Advanced Filter
  </button>

  <!-- Clear Filters -->
  <button type="button" id="clear-marks-filters" class="staffbar-btn">
    <i class="ri-refresh-line"></i>
    Clear Filters
  </button>

</div>






<style>
  #' . $searchId . '::placeholder { color:#111827; opacity:0.9; }
</style>

<br>
<br>


</div>

    <div class="table-responsive">
        <table id="marks_capture_table" class="table table-hover table-bordered marks_table" style="float:right; padding:0px; color:black; font-family:HP Simplified, Roboto, Tahoma, Geneva, Verdana, sans-serif; font-size: 10px;width: 100%;">

            <thead style="font-weight:bold;">
            <tr style="background-color: #F8F8F8 ; color:  	#000000;">
                <th>#</th>


                <th style="width:120px;max-width:120px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                   Reg Number </th>
                <th>Name</th>
                <th>Surname</th>';

if ($assessmentWeight > 0) {
    $assessmentIds = [];
    if (!empty($assessmentData)) {
        // Iterate through each row
        foreach ($assessmentData as $row) {
            // Access the value of the coursework_title column
            $assessmentTitle = $row['coursework_title'];
            $assessmentid =  $row['courseID'];
            $courseworkId = $courseworkIds[$i];

            // Add the assessment id to the array
            $assessmentIds[] = $assessmentid;

            // Add the coursework_title value as a new table column
            // If there is no courseweight, we don't need courseweight
            echo '<th datatype="numeric" class="assessment-column" id="' . $assessmentid . '" data-total-mark="' . $row['total_mark'] . '" style="font-family: Arial, sans-serif; font-size: 9px;">' . $assessmentTitle . '<br><b>(' . $row['total_mark'] . ')</b></th>';
        }
    }

    echo '<th id="cw">OCW</th>';
}

if ($otherExam != -1 && $otherExam != NULL) {
    echo '<th datatype = "numeric" id="otherExam">Other Exam</th>';
}
if ($examWeight != 0) {

    echo '<th datatype = "numeric" id = "exam" >Exam</th>';
}


echo '<th style="text-align: center; " datatype = "numeric"  id = "mark" >OM%</th>
        <th datatype = "numeric"  id = "grade" >Grade</th>
        <th datatype="numeric" id="courseCredit">Credits Att.</th>
        <th datatype = "numeric"  id = "remark" >Remark</th>
        <th datatype = "string"  id = "comment" >Comment</th>
        <th datatype = "string"  id = "posted" >Posted</th>
    </tr>
</thead>
<tbody data-toggle="tooltip" data-placement="top" title="CLICK TO SELECT THE STUDENT">



<div class="tab-container">
<div class="floating-tabs">




<!-- Active button -->
<button class="tab" id="export-pdf-button" style="padding: 14px 20px; position: relative; font-family: Arial, sans-serif;">
  <span class="tab-icon" style="height: 10px; width: 10px; background-color: green; border-radius: 50%; display: inline-block;"></span>
  &nbsp;Active
</button>
 <!-- Export to PDF button with an ID -->

  <!-- Export to PDF Button -->
<button class="tab export-pdf-button" id="export-pdf-button2"  style="padding: 5px 20px;">
  <span class="tab-icon"><i class="fa fa-file-pdf-o"></i></span>
  Export to PDF
</button>

<button class="tab" id="assessment-title-button" style="padding: 10px 20px;">
  <span class="tab-icon"><i class="fa fa-plus-square-o"></i></span>
  Capturing:&nbsp;<span id="assessment-title">--</span>
</button>

<!-- Marked Out of Button -->
<button class="tab" id="marked-out-of-button" style="padding: 10px 20px;">
  <span class="tab-icon"><i class="fa fa-pencil"></i></span>
  Marked out of:&nbsp;<span id="marked-out-of">--</span>
</button>



</div>




';


echo '
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.3.1/jspdf.umd.min.js"></script>
    <!-- Include the autoTable plugin -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.14/jspdf.plugin.autotable.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ho+j7jyWK8fNQe+A12Hb8AhRq26LrZ/JpcUGpOn+Y7RsweNrtN/tE3MoK7ZeZDyx" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.3.1/jspdf.umd.min.js"></script>
    <!-- Include the autoTable plugin -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.14/jspdf.plugin.autotable.min.js"></script>


';




//we removed the echo here



$selectedclass = $_GET['selectedclass'];

$class = '';

//echo $selectedclass;




// Base query
		$student_results_sql = "SELECT DISTINCT
										bi.FirstName AS StudentName,
										bi.Surname   AS StudentSurname,
										sp.part      AS yearofstudy,
										sp.semester  AS semester,
										bi.ID        AS StudentNumber,
										bi.Sex       AS Gender,
										s.Name       AS progName,
										sp.programme_code AS progcode,
										cl.year      AS academicyear,
										`ssl`.regulation_code AS regulationCode
									FROM student_progression sp
									INNER JOIN study s
										ON s.ID = '$selectedprogID'
									   AND s.ShortName = sp.programme_code
									   AND s.ProgrammesAvailable = 1
									INNER JOIN `basic-information` bi
										ON bi.ID = sp.student_id
									INNER JOIN courselecture cl
										ON cl.classcode = s.ID
									   AND cl.periodID  = sp.periodID
									   AND cl.part      = sp.part
									   AND cl.semester  = sp.semester
									
									LEFT JOIN (
										SELECT StudentID, MAX(regulation_code) AS regulation_code
										FROM `student-study-link`
										GROUP BY StudentID
									) `ssl`
										ON `ssl`.StudentID = sp.student_id
									WHERE sp.exam_centre = '$selectedcampus'
									  AND sp.format      = '$selectedformat'
									  AND sp.periodID    = '$periodID'
									  AND sp.part        = '$selectedyearofstudy'
									  AND sp.semester    = '$selectedsemester'
									";


// Modify the query to include filtering based on regulation codes if the filter is set



		if ($regulationCode) {
			if ($lastYearInRegulationCode >= 2023) {
				$student_results_sql .= "
          AND (
              `ssl`.regulation_code IS NULL
              OR CAST(RIGHT(`ssl`.regulation_code, 4) AS UNSIGNED) >= $lastYearInRegulationCode
          )";
			} else {
				$student_results_sql .= "
          AND CAST(RIGHT(`ssl`.regulation_code, 4) AS UNSIGNED) = $lastYearInRegulationCode";
			}
		}


		$student_results_sql .= " ORDER BY bi.Surname;";



//LIMIT 5


// echo $student_results_sql;
//
//die();

$student_results_sql_run = $this->core->database->doSelectQuery($student_results_sql);

// --------------------------------------------------------------------
// Pre-fetch ALL coursework and ALL exam results for this class ONCE
// --------------------------------------------------------------------

$courseworkByStudent = [];
if ($assessmentWeight > 0) {
    $allCourseworkSql = "SELECT 
                                cr.student_id,
                                cr.courseworkId,
                                cr.totalMark
                            FROM edurole.courseworkresults cr
                            INNER JOIN coursework cw ON cr.courseworkID = cw.ID
                            WHERE cw.lecturer_course_id = '$selectedclass'
                            GROUP BY cr.student_id, cr.courseworkId";

    $allCourseworkSql_run = $this->core->database->doSelectQuery($allCourseworkSql);

    while ($cwRow = $allCourseworkSql_run->fetch_assoc()) {
        $sid = $cwRow['student_id'];

        if (!isset($courseworkByStudent[$sid])) {
            $courseworkByStudent[$sid] = [];
        }

        $courseworkByStudent[$sid][] = [
            'courseworkId' => $cwRow['courseworkId'],
            'totalMark'    => $cwRow['totalMark']
        ];
    }
}

$examByStudent = [];
$allResultsSql = "SELECT *
                    FROM
                        edurole.courseresultssummary
                    WHERE 
                        courselecture_id = '$selectedclass'";

$allResultsSql_run = $this->core->database->doSelectQuery($allResultsSql);

while ($resultsRow = $allResultsSql_run->fetch_assoc()) {
    $sid = $resultsRow['student_id'];

    if (!isset($examByStudent[$sid])) {
        $examByStudent[$sid] = [];
    }

    $examByStudent[$sid][] = $resultsRow;
}


$i =0;


$allResults = []; // This will store our multi-dimensional array of results

while ($row = $student_results_sql_run->fetch_assoc()) {
    $student_number = $row["StudentNumber"];
    $i++;

    //echo $student_number; 
    $class = '';
    if ($row['progName'] != '') {
        $class = $row['progName'];
    }

    $classCode = '';
    if ($row['progcode'] != '') {
        $classCode = $row['progcode'];
    }

    $prog_short_code = '';
    if ($row['ShortName'] != '') {
        $prog_short_code = $row['ShortName'];
    }

    // Initialize the student data if not already done
    if (!isset($allResults[$student_number])) {
        $allResults[$student_number] = [
            'i' => $i,
            'RegNumber' => $row['StudentNumber'],
            'Name' => strtoupper($row['StudentName']),
            'Surname' => strtoupper($row['StudentSurname']),
            'part' => $row['part'],
            'programme' => $row['prog'],
            'semester' => $row['semester'],
            'year' => $row['year'],
            'className' => $class,
            'classCode' => $progcode,
            'gender' => isset($row['Gender']) ? $row['Gender'] : '',
            'related_records' => []
        ];
    }


    // course work will be fetched only if $assessmentWeight > 0
    if ($assessmentWeight > 0 && isset($courseworkByStudent[$student_number])) {

        foreach ($courseworkByStudent[$student_number] as $secondaryRow) {
            $allResults[$student_number]['related_records'][] = [
                'courseworkId' => $secondaryRow['courseworkId'],
                'totalMark' => $secondaryRow['totalMark']
            ];
        }
    }


    // Fetch the exam records for the current student
    if (isset($examByStudent[$student_number])) {

        // Fetch and add to the array, this arry will serve to keep if there are any, the results records for the students
        foreach ($examByStudent[$student_number] as $resultsRow) {
            $allResults[$student_number]['exam_records'][] = [
                'ID' => $resultsRow['ID'],
                'courseWorkMark' => $resultsRow['courseWorkMark'],
                'finalExaminationMark' => $resultsRow['finalExaminationMark'],
                //ADDED THE COLUMN THAT WILL HANDLE THE otherExam
                'overallMark' => $resultsRow['overallMark'],
                'otherExam' => $resultsRow['otherExam'],
                'resultGrade' => $resultsRow['resultGrade'],
                'courseRemark' => $resultsRow['courseRemark'],
                'comment' => $resultsRow['comment'],

                //added these for publishing board_reviewed = 1 published at department
                'board_reviewed' => $resultsRow['board_reviewed']

            ];
        }
    }


}
// statistics 4 populating the perfomance table

		$totalStudents       = 0;
		$totalWithMarks      = 0;
		$totalNoMarks        = 0;
		$passCount           = 0;
		$failCount           = 0;
		$distinctionCount    = 0;

		$sumOverall          = 0;
		$overallCount        = 0;
		$highestMark         = null;
		$highestStudentLabel = '';
		$lowestMark          = null;
		$lowestStudentLabel  = '';

		$perfStudents        = [];


// Data Rows
		foreach ($allResults as $student)
		{
			$examRecords = $student['exam_records'][0];

			$overallMark = isset($examRecords['overallMark']) ? (int)round($examRecords['overallMark']) : '';

			$genderAttr = isset($student['gender']) ? strtolower($student['gender']) : '';

			$rowStatus = 'no-marks';
			$hasMarks  = false;

			if ($overallMark !== '' && $overallMark !== null) {
				$hasMarks = true;
			} else {
				if (
					(isset($examRecords['finalExaminationMark']) && $examRecords['finalExaminationMark'] !== '' && $examRecords['finalExaminationMark'] !== null) ||
					(isset($examRecords['courseWorkMark']) && $examRecords['courseWorkMark'] !== '' && $examRecords['courseWorkMark'] !== null) ||
					(isset($examRecords['otherExam']) && $examRecords['otherExam'] !== '' && $examRecords['otherExam'] !== null)
				) {
					$hasMarks = true;
				}
			}

			// ✅ Status MUST be determined by courseRemark + resultGrade
			$courseRemarkRaw = $examRecords['courseRemark'] ?? '';
			$courseRemark    = strtolower(trim((string)$courseRemarkRaw));

			$resultGradeRaw  = $examRecords['resultGrade'] ?? '';
			$resultGrade     = strtoupper(trim((string)$resultGradeRaw));

			if ($courseRemark !== '') {
				if (strpos($courseRemark, 'pass') !== false) {
					$rowStatus = in_array($resultGrade, ['1', 'D'], true) ? 'distinction' : 'passed';
				} elseif (strpos($courseRemark, 'fail') !== false) {
					$rowStatus = 'failed';
				} else {
					$rowStatus = 'no-marks';
				}
			}

			// statistics for Performance tab
			$totalStudents++;

			if ($hasMarks) {
				$totalWithMarks++;
			} else {
				$totalNoMarks++;
			}

			// ✅ Passed should include Distinctions
			if ($rowStatus === 'passed' || $rowStatus === 'distinction') {
				$passCount++;
			}
			if ($rowStatus === 'distinction') {
				$distinctionCount++;
			}

			elseif (strtoupper(trim((string)($examRecords['resultGrade'] ?? ''))) !== ''
				&& strpos(strtoupper(trim((string)($examRecords['resultGrade'] ?? ''))), 'F') === 0)

			{
				$failCount++;
			}


			if ($overallMark !== '' && $overallMark !== null) {
				$sumOverall   += $overallMark;
				$overallCount++;

				$studentLabel = $student['RegNumber'] . ' - ' . $student['Name'] . ' ' . $student['Surname'];

				if ($highestMark === null || $overallMark > $highestMark) {
					$highestMark         = $overallMark;
					$highestStudentLabel = $studentLabel;
				}

				if ($lowestMark === null || $overallMark < $lowestMark) {
					$lowestMark         = $overallMark;
					$lowestStudentLabel = $studentLabel;
				}
			}

			$perfStudents[] = [
				'reg'     => $student['RegNumber'],
				'name'    => $student['Name'],
				'surname' => $student['Surname'],
				'overall' => ($overallMark !== '' && $overallMark !== null) ? $overallMark : null,
				'status'  => $rowStatus,
				'gender'  => $genderAttr,
			];



    
    echo '<tr id="' . $student['RegNumber'] . '" data-gender="' . htmlspecialchars($genderAttr) . '" data-status="' . $rowStatus . '" data-has-marks="' . ($hasMarks ? '1' : '0') . '">';

    echo '<td style = "background-color: #F0F0F0;">' . $student['i'] . '</td>';
    echo '<td style = "background-color: #F0F0F0;"><b>' . $student['RegNumber'] . '</b></td>';
    echo '<td style = "background-color: #F0F0F0;">' . $student['Name'] . '</td>';
    echo '<td style = "background-color: #F0F0F0;">' . $student['Surname'] . '</td>';

    
    
    
    
    

    $boardStatus = isset($examRecords['board_reviewed']) ? (int)$examRecords['board_reviewed'] : null;

    
    $rowEditableClass   = ($boardStatus === null || $boardStatus < 0) ? 'editable' : '';

    
    $cwEditableClass    = ($boardStatus === null || $boardStatus === -1) ? 'editable' : '';

    
    $examEditableClass  = ($boardStatus === null || $boardStatus < 0) ? 'editable' : '';

    
    $overallMarkStyle = '';
    $overallMarkStyle2 = '';
    if (isset($examRecords['overallMark']) && $examRecords['overallMark'] < 50) {
        $overallMarkStyle = ' style="color: red;"';
        $overallMarkStyle2 = ' color: red;';
    }

    
    if ($assessmentWeight > 0) {
        
        foreach ($assessmentData as $assessment) {
            $courseworkId = $assessment['courseID'];

            
            $matchingRecord = array_filter($student['related_records'], function ($record) use ($courseworkId) {
                return $record['courseworkId'] == $courseworkId;
            });

            
            if (!empty($matchingRecord)) {
                
                $matchingRecord = reset($matchingRecord);

                
                $value = $matchingRecord['totalMark'];

                echo '<td class="' . $cwEditableClass . '" ' . $overallMarkStyle . ' data-coursework-id="' . $courseworkId . '">' . $value . '</td>';
            } else {
                
                echo '<td class="' . $cwEditableClass . '" ' . $overallMarkStyle . ' data-coursework-id="' . $courseworkId . '"></td>';
            }
        }

        echo '<td ' . $overallMarkStyle . ' style="background-color: #F0F0F0;">' . htmlspecialchars($examRecords['courseWorkMark'] ?? '') . '</td>';
    }

    
    
    if ($examWeight != -1 && ($otherExam != -1)) {
        echo '<td class="' . $examEditableClass . '"' . $overallMarkStyle . '>'. htmlspecialchars($examRecords['otherExam'] ?? '') . '</td>';
    }

    
    if ($examWeight != 0 )
    {
        echo '<td class="' . $examEditableClass . '"' . $overallMarkStyle . '>'. htmlspecialchars($examRecords['finalExaminationMark'] ?? '') . '</td>';
    }

    
    echo '<td  style="text-align: center; font-weight: bold;background-color: #F0F0F0;' . $overallMarkStyle2 . '">' . htmlspecialchars($overallMark) . '</td>';

    echo '<td ' . $overallMarkStyle . 'style = "background-color: #F0F0F0;">' . htmlspecialchars($examRecords['resultGrade'] ?? '') . '</td>';

// credits col
        $grade = trim($examRecords['resultGrade'] ?? '');

        $creditDisplay = '';
        if ($grade !== '') {
            $creditDisplay = (stripos($grade, 'pass') !== false) ? ($CourseCredits ?? 0) : 0;
        }

        echo '<td ' . $overallMarkStyle . 'style="background-color: #F0F0F0;">' . htmlspecialchars((string)$creditDisplay) . '</td>';






    echo '<td ' . $overallMarkStyle . 'style = "background-color: #F0F0F0;">'. htmlspecialchars($examRecords['courseRemark'] ?? '') . '</td>';

    
    echo '<td class="' . $examEditableClass . '"' . $overallMarkStyle . '>' . htmlspecialchars($examRecords['comment'] ?? '') . '</td>';

    
    
    
    $postedIcon = '';

    if ($boardStatus === -1 || $boardStatus === null) {
        
        $postedIcon = '<i class="ri-checkbox-blank-line" style="font-size:14px;color:#000;"></i>';
    } elseif ($boardStatus === -2) {
        
        $postedIcon = '<i class="ri-check-line" style="font-size:15px;color:#000;"></i>';
    } elseif ($boardStatus === 0) {
        
        $postedIcon = '<i class="ri-check-double-line" style="font-size:15px;color:#16a34a;"></i>';
    } else {
        
        $postedIcon = '<i class="ri-check-double-line" style="font-size:15px;color:#16a34a;"></i>';
    }

    echo '<td style="background-color:#F0F0F0;text-align:center;">' . $postedIcon . '</td>';

}



// ----------------------------------------------------------------------
// Finalise statistic expose to jquery
// ----------------------------------------------------------------------
      $passRate  = 0;
      $failRate  = 0;

      if ($totalWithMarks > 0) {
          $passRate = round(($passCount / $totalWithMarks) * 100);
          $failRate = round(($failCount / $totalWithMarks) * 100);
      }

      $classAverage = ($overallCount > 0) ? round($sumOverall / $overallCount, 1) : null;

      echo '
      <script>
        window.marksPerfStats = ' . json_encode([
            'totalStudents'    => (int)$totalStudents,
            'postedCount'      => (int)$totalWithMarks,
            'passRate'         => (int)$passRate,
            'failRate'         => (int)$failRate,
            'capturedCount'    => (int)$totalWithMarks,
            'noMarksCount'     => (int)$totalNoMarks,
            'highestMark'      => $highestMark,
            'highestStudent'   => $highestStudentLabel,
            'lowestMark'       => $lowestMark,
            'lowestStudent'    => $lowestStudentLabel,
            'classAverage'     => $classAverage,
            'distinctionCount' => (int)$distinctionCount,
        ]) . ';

        window.marksPerfStudents = ' . json_encode($perfStudents) . ';
      </script>
      ';


      








echo '
<div style="max-width: 25000px;" class="modal fade" id="validationAlertModal" tabindex="-1" role="dialog" aria-labelledby="validationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md" role="document">
        <div class="modal-content">

            <div style="padding: 15px; background-color: #f2f2f2;" class="modal-header"> <!-- Increased padding and changed background color -->

                <h6 class="modal-title" id="validationModalLabel">
                    &nbsp;&nbsp;<i class="fa fa-exclamation-triangle"></i> &nbsp;&nbsp;Validation Error
                </h6>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p id="validationAlertText">
                    &nbsp;&nbsp;<i class="fa fa-infor"></i> &nbsp;&nbsp;&nbsp;&nbsp;The message will go here.
                </p>
            </div>
            <div class="modal-footer">
            <button style="float:right; display: inline-block; outline: 0; cursor: pointer; border-radius: 8px; box-shadow: 0 2px 5px 0 rgb(213 217 217 / 50%); background: #FFF; border: 1px solid #D5D9D9; font-size: 11px; font-weight: bold; height: 30px; padding: 0 50px; /* Increased padding */ text-align: center; color: #0F1111;" type="button" class="btn btn-secondary" data-dismiss="modal">
            <i class="fa fa-check"></i> OK
        </button>

            </div>
        </div>
    </div>
</div>

    <div class="modal fade" id="ErrorModal" tabindex="-1" role="dialog" aria-labelledby="ErrorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ErrorModalLabel">
                    <i class="fa fa-info-circle"></i>&nbsp; Error
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Posting failed. Try again.
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>


<div
  class="modal fade"
  id="SuccessModal"
  tabindex="-1"
  role="dialog"
  aria-labelledby="SuccessModalLabel"
  aria-hidden="true"
  style="font-family: \'Roboto\', Calibri, Segoe UI, Arial, sans-serif; font-size: 15px;"
>
  <div class="modal-dialog modal-dialog-centered modal-md" role="document">
    <div class="modal-content" style="
        background:#ffffff;
        border-radius:6px;
        border:1px solid #111111;                 /* slim black outer line */
        box-shadow:0 12px 30px rgba(0,0,0,0.18);
        overflow:hidden;
      ">

      <!-- Header -->
      <div class="modal-header" style="
          padding:14px 18px;
          border-bottom:1px solid #111111;        /* slim black divider */
          background:#ffffff;
        ">
        <h5
          class="modal-title"
          id="SuccessModalLabel"
          style="display:flex;align-items:center;gap:10px;font-weight:700;font-size:16px;margin:0;"
        >
          <i class="ri-checkbox-circle-line" aria-hidden="true" style="font-size:20px;line-height:1;"></i>
          <span>Success</span>
        </h5>
        <button
          type="button"
          class="close"
          data-dismiss="modal"
          aria-label="Close"
          style="outline:none;"
        >
          <span aria-hidden="true" style="font-size:22px;">&times;</span>
        </button>
      </div>

      <!-- Body -->
      <div class="modal-body" style="padding:18px 18px 12px 18px; line-height:1.55;">
        <p style="margin-bottom:10px;">
          All marks were successfully posted. Do you want to export the marks now?
        </p>

      <p style="margin-bottom:0;color:#6b7280;font-size:11px; line-height:1.35; display:flex; gap:8px; align-items:flex-start;">
        <i class="ri-information-line" aria-hidden="true" style="font-size:14px; line-height:1; margin-top:1px;"></i>
        <span>
          When you click <strong>Export</strong>, the PDF may open in a new tab.
          If it does, use the browser PDF viewer’s <strong>Download</strong> button to save it.
        </span>
      </p>

      </div>


      <!-- Footer -->
      <div class="modal-footer" style="
          padding:12px 16px 14px 16px;
          border-top:1px solid #111111;           /* slim black divider */
          background:#ffffff;
          display:flex;
          justify-content:flex-end;
          align-items:center;
          gap:8px;
        ">

        <!-- Export (YES) - same button concept -->
       <a
        href="'.$exportUrl.'"
        target="_blank"
        rel="noopener"
        class="btn btn-sm"
        title="Export marks"
        onmouseover="this.style.background=\'#f3f4f6\'"
        onmouseout="this.style.background=\'#ffffff\'"
        onfocus="this.style.outline=\'none\'"
        onblur="this.style.outline=\'none\'"
        onclick="
          window.open(this.href, \'_blank\', \'noopener\');
          try { $(\'#SuccessModal\').modal(\'hide\'); } catch(e) {}
          setTimeout(goBack, 500);
          return false;
        "
        style="
          display:inline-flex;align-items:center;gap:10px;
          padding:8px 16px;
          background:#ffffff;
          color:#000000;
          border:1px solid #111111;
          border-radius:6px;
          box-shadow:none;
          cursor:pointer;
          font-size:13px;
          font-weight:700;
          text-decoration:none;
        "
      >
  <i class="ri-file-pdf-2-line" aria-hidden="true" style="font-size:18px;line-height:1;"></i>
  <span style="color:#000000;">Export</span>
</a>

        <!-- Pipe -->
        <span aria-hidden="true" style="padding:0 4px;color:#111111;">|</span>

        <!-- No / Back -->
        <button
          type="button"
          class="btn btn-sm"
          onclick="window.history.back();"
          title="Go back"
          onmouseover="this.style.background=\'#f3f4f6\'"
          onmouseout="this.style.background=\'#ffffff\'"
          onfocus="this.style.outline=\'none\'"
          onblur="this.style.outline=\'none\'"
          style="
            display:inline-flex;align-items:center;gap:8px;
            padding:8px 14px;
            background:#ffffff;
            color:#111827;
            border:1px solid #111111;
            border-radius:8px;
            box-shadow:none;
            cursor:pointer;
            font-size:13px;
            font-weight:500;
          "
        >
          <i class="ri-arrow-go-back-line" aria-hidden="true" style="font-size:16px;line-height:1;"></i>
          <span>Not now</span>
        </button>

      </div>
    </div>
  </div>
</div>



';


//modal for coursework

  



		echo '
<div style="max-width: 15000px;" class="modal fade" id="InformationAlertModal" tabindex="-1" role="dialog" aria-labelledby="validationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md" role="document">
        <div class="modal-content">
            <div style="padding: 12px; background-color: #f2f2f2;" class="modal-header"> 
                <h6 class="modal-title" id="validationModalLabel"> <!-- Changed the heading size -->
                    &nbsp;&nbsp;<i class="fa fa-info-circle"></i> &nbsp;&nbsp;Confirm Mark
                </h6>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p id="validationAlertText">
                    
                </p>
            </div>
            <div class="modal-footer">
                <button id="confirmButton"  style="float:right; display: inline-block; outline: 0; cursor: pointer; border-radius: 8px; box-shadow: 0 2px 5px 0 rgb(213 217 217 / 50%); background: #FFF; border: 1px solid #D5D9D9; font-size: 11px; font-weight: bold; height: 30px; padding: 0 50px; text-align: center; color: #0F1111;" type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fa fa-check"></i> Confirm
                </button>
				<button id="confirmCancel" style="float:right; display: inline-block; outline: 0; cursor: pointer; border-radius: 8px; box-shadow: 0 2px 5px 0 rgba(0, 0, 0, 0.5); background: #333; /* Dark fill */ border: 1px solid #333; font-size: 11px; font-weight: bold; height: 30px; padding: 0 50px; text-align: center; color: #FFF; /* White text */" type="button" class="btn btn-secondary" data-dismiss="modal">
					<i class="fa fa-times" style="color: #FFF;"></i> Cancel <!-- White icon -->
				</button>

            </div>
        </div>
    </div>
</div>


<div
  class="modal fade"
  id="confirm"
  tabindex="-1"
  role="dialog"
  aria-labelledby="exampleModalCenterTitle"
  aria-hidden="true"
  style="font-family: \'Roboto\', Calibri, Segoe UI, Arial, sans-serif; font-size: 15px;"
>
  <div class="modal-dialog modal-md modal-dialog-centered" role="document" style="z-index:1050;">
    <div class="modal-content" style="
        background:#ffffff;
        border-radius: 6px;
        border: 1px solid #111111;
        box-shadow: 0 12px 30px rgba(0,0,0,0.18);
        overflow: hidden;
        min-height: 270px;
      ">

      <!-- Header -->
      <div class="modal-header" style="
          padding: 14px 18px;
          border-bottom: 1px solid #111111;
          background: #ffffff;
        ">
        <h5
          class="modal-title"
          id="confirms"
          style="display:flex;align-items:center;gap:10px;font-weight:700;font-size:16px;margin:0;"
        >
          <i class="ri-information-line" aria-hidden="true" style="font-size:20px;line-height:1;"></i>
          <span>Confirm Results Posting</span>
        </h5>
        <button
          type="button"
          class="close"
          data-dismiss="modal"
          aria-label="Close"
          style="outline:none;"
        >
          <span aria-hidden="true" style="font-size:22px;">&times;</span>
        </button>
      </div>

      <!-- Body -->
      <div class="modal-body" style="padding:18px 18px 12px 18px; line-height:1.55;">
        <p id="modal-par" style="margin-bottom:10px;">
          You are about to post both <strong>captured coursework</strong> and <strong>captured examination</strong>
          marks for this course. Do you want to continue?
        </p>

        <!-- Muted note -->
        <p style="margin-bottom:0;color:#6b7280;font-size:13px;">
          Once posted, the results will be <strong>view-only</strong> and
          no further changes will be allowed.
        </p>
      </div>

      <!-- Footer -->
      <div class="modal-footer" style="
          padding:12px 16px 14px 16px;
          border-top: 1px solid #111111;
          background:#ffffff;
          display:flex;
          justify-content:flex-end;
          align-items:center;
          gap:8px;
        ">

        <!-- Cancel -->
        <button
          type="button"
          class="btn btn-sm"
          data-dismiss="modal"
          onmouseover="this.style.background=\'#f3f4f6\'"
          onmouseout="this.style.background=\'#ffffff\'"
          onfocus="this.style.outline=\'none\'"
          onblur="this.style.outline=\'none\'"
          style="
            display:inline-flex;align-items:center;gap:8px;
            padding:8px 14px;
            background:#ffffff;
            color:#111827;
            border:1px solid #111111;
            border-radius:8px;
            box-shadow:none;
            cursor:pointer;
            font-size:13px;
            font-weight:500;
          "
        >
          <i class="ri-close-line" aria-hidden="true" style="font-size:16px;line-height:1;"></i>
          <span>Cancel</span>
        </button>

        <!-- Pipe -->
        <span aria-hidden="true" style="padding:0 4px;color:#111111;">|</span>

        <!-- Continue -->
        <button
          id="ok-postmodal"
          type="button"
          class="btn btn-sm"
          data-dismiss="modal"
          title="Continue"
          onmouseover="this.style.background=\'#f3f4f6\'"
          onmouseout="this.style.background=\'#ffffff\'"
          onfocus="this.style.outline=\'none\'"
          onblur="this.style.outline=\'none\'"
          style="
            display:inline-flex;align-items:center;gap:10px;
            padding:8px 16px;
            background:#ffffff;
            color:#000000;
            border:1px solid #111111;
            border-radius:6px;
            box-shadow:none;
            cursor:pointer;
            font-size:13px;
            font-weight:700;
          "
        >
          <i class="ri-arrow-right-line" aria-hidden="true" style="font-size:18px;line-height:1;"></i>
          <span style="color:#000000;">Continue</span>
        </button>

      </div>

    </div>
  </div>
</div>



<!-- confirm posting coursework only-->



<div
  class="modal fade"
  id="confirm-courseworkonly"
  tabindex="-1"
  role="dialog"
  aria-labelledby="exampleModalCenterTitle"
  aria-hidden="true"
  style="font-family: \'Roboto\', Calibri, Segoe UI, Arial, sans-serif; font-size: 15px;"
>
  <div class="modal-dialog modal-md modal-dialog-centered" role="document" style="z-index:1050;">
    <div class="modal-content" style="
        background:#ffffff;
        border-radius: 6px;
        border: 1px solid #111111;
        box-shadow: 0 12px 30px rgba(0,0,0,0.18);
        overflow: hidden;
        min-height: 270px;
      ">

      <!-- Header -->
      <div class="modal-header" style="
          padding: 14px 18px;
          border-bottom: 1px solid #111111;
          background: #ffffff;
        ">
        <h5
          class="modal-title"
          id="confirms"
          style="display:flex;align-items:center;gap:10px;font-weight:700;font-size:16px;margin:0;"
        >
          <i class="ri-information-line" aria-hidden="true" style="font-size:20px;line-height:1;"></i>
          <span>Confirm Course Assessment Posting</span>
        </h5>
        <button
          type="button"
          class="close"
          data-dismiss="modal"
          aria-label="Close"
          style="outline:none;"
        >
          <span aria-hidden="true" style="font-size:22px;">&times;</span>
        </button>
      </div>

      <!-- Body -->
      <div class="modal-body" style="padding:18px 18px 12px 18px; line-height:1.55;">
        <p id="modal-par" style="margin-bottom:10px;">
          You are about to post <strong>captured course assessment</strong> marks for this course.
          Examination marks will <strong>not</strong> be posted. Do you want to continue?
        </p>

        <!-- Muted note -->
        <p style="margin-bottom:0;color:#6b7280;font-size:11px; line-height:1.35; display:flex; gap:8px; align-items:flex-start;">
          <i class="ri-information-line" aria-hidden="true" style="font-size:14px; line-height:1; margin-top:1px;"></i>
          <span>
            Once posted, the course assessment marks will be <strong>view-only</strong> and no further changes will be allowed.
          </span>
        </p>
      </div>

      <!-- Footer -->
      <div class="modal-footer" style="
          padding:12px 16px 14px 16px;
          border-top: 1px solid #111111;
          background:#ffffff;
          display:flex;
          justify-content:flex-end;
          align-items:center;
          gap:8px;
        ">

        <!-- Cancel -->
        <button
          type="button"
          class="btn btn-sm"
          data-dismiss="modal"
          onmouseover="this.style.background=\'#f3f4f6\'"
          onmouseout="this.style.background=\'#ffffff\'"
          onfocus="this.style.outline=\'none\'"
          onblur="this.style.outline=\'none\'"
          style="
            display:inline-flex;align-items:center;gap:8px;
            padding:8px 14px;
            background:#ffffff;
            color:#111827;
            border:1px solid #111111;
            border-radius:8px;
            box-shadow:none;
            cursor:pointer;
            font-size:13px;
            font-weight:500;
          "
        >
          <i class="ri-close-line" aria-hidden="true" style="font-size:16px;line-height:1;"></i>
          <span>Cancel</span>
        </button>

        <!-- Pipe -->
        <span aria-hidden="true" style="padding:0 4px;color:#111111;">|</span>

        <!-- Continue (post course assessment only) -->
        <button
          id="ok-postcourseworkmodal"
          type="button"
          class="btn btn-sm"
          data-dismiss="modal"
          title="Continue"
          onmouseover="this.style.background=\'#f3f4f6\'"
          onmouseout="this.style.background=\'#ffffff\'"
          onfocus="this.style.outline=\'none\'"
          onblur="this.style.outline=\'none\'"
          style="
            display:inline-flex;align-items:center;gap:10px;
            padding:8px 16px;
            background:#ffffff;
            color:#000000;
            border:1px solid #111111;
            border-radius:6px;
            box-shadow:none;
            cursor:pointer;
            font-size:13px;
            font-weight:700;
          "
        >
          <i class="ri-check-line" aria-hidden="true" style="font-size:18px;line-height:1;"></i>
          <span style="color:#000000;">Post Course Assessment</span>
        </button>

      </div>

    </div>
  </div>
</div>








<!-- Modal for recalculate the marks the normal ones -->
<div class="modal fade" id="processingModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
        <div class="modal-content text-center" style="font-family: Calibri, sans-serif; font-size: 14px;">
            <div class="modal-body py-4">
                <i class="fas fa-spinner fa-spin fa-2x text-primary mb-3"></i>
                <p class="mb-2">Processing Student: <strong id="current-student">N/A</strong></p>
                <div class="progress" style="height: 25px;">
                    <div id="progress-bar" class="progress-bar progress-bar-striped progress-bar-animated" 
                         role="progressbar" style="width: 0%;" 
                         aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                        <span id="progress-text">0%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>







<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.7/dist/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>

<script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.js"></script>
<script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.7/dist/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">


'




		;


//////end of fetch

		echo'


					</tr>
					</tbody>
				</table>



<script>
  $(function () {
    console.log(\'[marks-filter] document.ready fired\');

    // Basic sanity checks
    if (!$(\'#marks_capture_table\').length) {
      console.log(\'[marks-filter] ERROR: #marks_capture_table NOT FOUND\');
      return;
    } else {
      console.log(\'[marks-filter] #marks_capture_table found\');
    }

    if (!$(\'#marks-filter\').length) {
      console.log(\'[marks-filter] ERROR: #marks-filter NOT FOUND\');
      return;
    } else {
      console.log(\'[marks-filter] #marks-filter found\');
    }

    if (!$(\'#marks_capture_table tbody\').length) {
      console.log(\'[marks-filter] WARNING: #marks_capture_table tbody NOT FOUND\');
    }

    // ---------------------------------------------------
    // Build counts from the current table rows
    // ---------------------------------------------------
    function buildMarksFilterCounts() {
      console.log(\'[marks-filter] buildMarksFilterCounts() called\');

      var counts = {
        all: 0,
        passed: 0,
        failed: 0,
        distinctions: 0,
        males: 0,
        females: 0,
        no_marks: 0,
        captured: 0
      };

      $(\'#marks_capture_table tbody tr\').each(function (idx) {
        var $tr = $(this);

        // Skip non-data rows
        if ($tr.find(\'td\').length === 0) {
          console.log(\'[marks-filter] row\', idx, \'skipped (no <td> cells)\');
          return;
        }

        counts.all++;

        var status   = $tr.data(\'status\');          // passed / failed / distinction / no-marks
        var gender   = ($tr.data(\'gender\') || \'\').toString().toLowerCase();
        var hasMarks = $tr.data(\'has-marks\') == 1;

        console.log(\'[marks-filter] row\', idx, {
          reg: $tr.attr(\'id\'),
          status: status,
          gender: gender,
          hasMarks: hasMarks
        });

        if (status === \'passed\')      counts.passed++;
        if (status === \'failed\')      counts.failed++;
        if (status === \'distinction\') counts.distinctions++;

        if (!hasMarks) counts.no_marks++;
        if (hasMarks)  counts.captured++;

        if (gender === \'m\' || gender === \'male\')   counts.males++;
        if (gender === \'f\' || gender === \'female\') counts.females++;
      });

      console.log(\'[marks-filter] FINAL COUNTS:\', counts);
      window.marksFilterCounts = counts; // for debugging in console
      return counts;
    }

    // ---------------------------------------------------
    // Apply counts to the <select> labels (prefix numbers)
    // ---------------------------------------------------
    function applyMarksFilterCounts() {
      console.log(\'[marks-filter] applyMarksFilterCounts() called\');

      var counts = buildMarksFilterCounts();

      var valueToKey = {
        \'all\':         \'all\',
        \'passed\':      \'passed\',
        \'failed\':      \'failed\',
        \'distinctions\':\'distinctions\',
        \'males\':       \'males\',
        \'females\':     \'females\',
        \'no-marks\':    \'no_marks\',
        \'captured\':    \'captured\'
      };

      console.log(\'[marks-filter] updating <option> labels on #marks-filter\');

      $(\'#marks-filter option\').each(function (idx) {
        var $opt = $(this);
        var val  = $opt.val();
        var key  = valueToKey[val];

        console.log(\'[marks-filter] option index\', idx, \'value=\', val, \'mapped key=\', key);

        if (!key) {
          // Option not mapped (e.g. placeholder)
          return;
        }

        // Remove old "12 - " prefix if present
        var originalText = $opt.text();
        var baseLabel = originalText.replace(/^\\d+\\s*-\\s*/, \'\');
        var newLabel  = counts[key] + \' - \' + baseLabel;

        console.log(\'[marks-filter]  "\', originalText, \'" -> "\', newLabel, \'"\');

        $opt.text(newLabel);
      });
    }

    // ---------------------------------------------------
    // Show/hide rows based on current filter value
    // ---------------------------------------------------
    function filterMarksTable() {
      var selected = $(\'#marks-filter\').val();
      console.log(\'[marks-filter] filterMarksTable() called, selected =\', selected);

      $(\'#marks_capture_table tbody tr\').each(function (idx) {
        var $tr = $(this);

        if ($tr.find(\'td\').length === 0) {
          return;
        }

        var show    = true;
        var gender  = ($tr.data(\'gender\') || \'\').toString().toLowerCase();
        var status  = $tr.data(\'status\');          // passed / failed / distinction / no-marks
        var hasMarks = $tr.data(\'has-marks\') == 1;

        switch (selected) {
          case \'all\':
            show = true;
            break;

          case \'passed\':
            show = (status === \'passed\');
            break;

          case \'failed\':
            show = (status === \'failed\');
            break;

          case \'distinctions\':
            show = (status === \'distinction\');
            break;

          case \'males\':
            show = (gender === \'m\' || gender === \'male\');
            break;

          case \'females\':
            show = (gender === \'f\' || gender === \'female\');
            break;

          case \'no-marks\':
            show = !hasMarks;
            break;

          case \'captured\':
            show = hasMarks;
            break;
        }

        if (!show) {
          // console.log(\'[marks-filter] hiding row\', idx, \'id=\', $tr.attr(\'id\'));
        }

        $tr.toggle(show);
      });
    }

    // ---------------------------------------------------
    // Wire up events
    // ---------------------------------------------------
    applyMarksFilterCounts();   // build + paint counts
    filterMarksTable();         // initial filter (All)

    $(\'#marks-filter\').on(\'change\', function () {
      console.log(\'[marks-filter] #marks-filter changed to\', this.value);
      filterMarksTable();
    });

    $(\'#clear-marks-filters\').on(\'click\', function () {
      console.log(\'[marks-filter] #clear-marks-filters clicked\');
      $(\'#marks-filter\').val(\'all\');
      filterMarksTable();
    });
  });
</script>





<script>
  $(function () {
    console.log(\'[marks-filter] document.ready fired\');

    if (!$(\'#marks_capture_table\').length) {
      console.log(\'[marks-filter] ERROR: #marks_capture_table NOT FOUND\');
      return;
    } else {
      console.log(\'[marks-filter] #marks_capture_table found\');
    }

    if (!$(\'#marks-filter\').length) {
      console.log(\'[marks-filter] ERROR: #marks-filter NOT FOUND\');
      return;
    } else {
      console.log(\'[marks-filter] #marks-filter found\');
    }

    if (!$(\'#marks-sortby\').length) {
      console.log(\'[marks-sort] WARNING: #marks-sortby NOT FOUND\');
    }
    if (!$(\'#marks-sortdir\').length) {
      console.log(\'[marks-sort] WARNING: #marks-sortdir NOT FOUND\');
    }

    var marksSortConfig = {};
    var numericSortCols = [\'ocw\', \'om\'];

    function initSortConfig() {
      console.log(\'[marks-sort] initSortConfig() called\');

      var $headerCells = $(\'#marks_capture_table thead tr\').first().children(\'th\');
      console.log(\'[marks-sort] header cells found:\', $headerCells.length);

      $headerCells.each(function (index) {
        var $th   = $(this);
        var text  = $.trim($th.text()).toLowerCase();
        var id    = $th.attr(\'id\') || \'\';

        console.log(\'[marks-sort] th index\', index, \'id=\', id, \'text=\', text);

        if (text === \'name\') {
          marksSortConfig.name = index;
        }
        if (text === \'surname\') {
          marksSortConfig.surname = index;
        }
      });

      var ocwIdx = $headerCells.filter(\'#cw\').index();
      if (ocwIdx >= 0) marksSortConfig.ocw = ocwIdx;

      var omIdx = $headerCells.filter(\'#mark\').index();
      if (omIdx >= 0) marksSortConfig.om = omIdx;

      var remarkIdx = $headerCells.filter(\'#remark\').index();
      if (remarkIdx >= 0) marksSortConfig.remark = remarkIdx;

      console.log(\'[marks-sort] final config:\', marksSortConfig);
    }

    function parseNumeric(val) {
      if (val == null) return null;
      var cleaned = String(val).replace(/[^0-9.\-]/g, \'\');
      if (cleaned === \'\') return null;
      var num = parseFloat(cleaned);
      return isNaN(num) ? null : num;
    }

    function buildMarksFilterCounts() {
      console.log(\'[marks-filter] buildMarksFilterCounts() called\');

      var counts = {
        all: 0,
        passed: 0,
        failed: 0,
        distinctions: 0,
        males: 0,
        females: 0,
        no_marks: 0,
        captured: 0
      };

      $(\'#marks_capture_table tbody tr\').each(function (idx) {
        var $tr = $(this);

        if ($tr.find(\'td\').length === 0) {
          console.log(\'[marks-filter] row\', idx, \'skipped (no <td> cells)\');
          return;
        }

        counts.all++;

        var status    = $tr.data(\'status\');
        var gender    = ($tr.data(\'gender\') || \'\').toString().toLowerCase();
        var hasMarks  = $tr.data(\'has-marks\') == 1;

        console.log(\'[marks-filter] row\', idx, {
          reg: $tr.attr(\'id\'),
          status: status,
          gender: gender,
          hasMarks: hasMarks
        });

        if (status === \'passed\')      counts.passed++;
        if (status === \'failed\')      counts.failed++;
        if (status === \'distinction\') counts.distinctions++;

        if (!hasMarks) counts.no_marks++;
        if (hasMarks)  counts.captured++;

        if (gender === \'m\' || gender === \'male\')   counts.males++;
        if (gender === \'f\' || gender === \'female\') counts.females++;
      });

      console.log(\'[marks-filter] FINAL COUNTS:\', counts);
      window.marksFilterCounts = counts;
      return counts;
    }

    function applyMarksFilterCounts() {
      console.log(\'[marks-filter] applyMarksFilterCounts() called\');

      var counts = buildMarksFilterCounts();

      var valueToKey = {
        \'all\':         \'all\',
        \'passed\':      \'passed\',
        \'failed\':      \'failed\',
        \'distinctions\':\'distinctions\',
        \'males\':       \'males\',
        \'females\':     \'females\',
        \'no-marks\':    \'no_marks\',
        \'captured\':    \'captured\'
      };

      console.log(\'[marks-filter] updating <option> labels on #marks-filter\');

      $(\'#marks-filter option\').each(function (idx) {
        var $opt = $(this);
        var val  = $opt.val();
        var key  = valueToKey[val];

        console.log(\'[marks-filter] option index\', idx, \'value=\', val, \'mapped key=\', key);

        if (!key) return;

        var originalText = $opt.text();
        var baseLabel    = originalText.replace(/^\\d+\\s*-\\s*/, \'\');
        var newLabel     = counts[key] + \' - \' + baseLabel;

        console.log(\'[marks-filter]  "\', originalText, \'" -> "\', newLabel, \'"\');
        $opt.text(newLabel);
      });
    }

    function filterMarksTable() {
      var selected = $(\'#marks-filter\').val() || \'all\';
      console.log(\'[marks-filter] filterMarksTable() called, selected =\', selected);

      $(\'#marks_capture_table tbody tr\').each(function (idx) {
        var $tr = $(this);

        if ($tr.find(\'td\').length === 0) {
          return;
        }

        var show     = true;
        var gender   = ($tr.data(\'gender\') || \'\').toString().toLowerCase();
        var status   = $tr.data(\'status\');
        var hasMarks = $tr.data(\'has-marks\') == 1;

        switch (selected) {
          case \'all\':
            show = true;
            break;

          case \'passed\':
            show = (status === \'passed\');
            break;

          case \'failed\':
            show = (status === \'failed\');
            break;

          case \'distinctions\':
            show = (status === \'distinction\');
            break;

          case \'males\':
            show = (gender === \'m\' || gender === \'male\');
            break;

          case \'females\':
            show = (gender === \'f\' || gender === \'female\');
            break;

          case \'no-marks\':
            show = !hasMarks;
            break;

          case \'captured\':
            show = hasMarks;
            break;
        }

        $tr.toggle(show);
      });
    }

    function sortMarksTable() {
      if ($.isEmptyObject(marksSortConfig)) {
        initSortConfig();
      }

      var sortBy  = $(\'#marks-sortby\').val() || \'name\';
      var sortDir = $(\'#marks-sortdir\').val() || \'desc\';
      var colIndex = marksSortConfig[sortBy];

      console.log(\'[marks-sort] sortMarksTable() sortBy=\', sortBy, \'sortDir=\', sortDir, \'colIndex=\', colIndex);

      if (typeof colIndex === \'undefined\' || colIndex < 0) {
        console.log(\'[marks-sort] no valid column index for sortBy=\', sortBy, \' – skipping sort\');
        return;
      }

      var $tbody = $(\'#marks_capture_table tbody\');
      var rows = $tbody.find(\'tr\').filter(function () {
        return $(this).find(\'td\').length > 0;
      }).get();

      console.log(\'[marks-sort] total rows to sort:\', rows.length);

      rows.sort(function (a, b) {
        var $a = $(a), $b = $(b);

        var aVal = $.trim($a.children(\'td\').eq(colIndex).text());
        var bVal = $.trim($b.children(\'td\').eq(colIndex).text());

        var cmp = 0;

        if (numericSortCols.indexOf(sortBy) !== -1) {
          var aNum = parseNumeric(aVal);
          var bNum = parseNumeric(bVal);

          if (aNum === null && bNum === null) cmp = 0;
          else if (aNum === null) cmp = 1;
          else if (bNum === null) cmp = -1;
          else cmp = aNum - bNum;
        } else {
          aVal = aVal.toLowerCase();
          bVal = bVal.toLowerCase();

          if (aVal < bVal) cmp = -1;
          else if (aVal > bVal) cmp = 1;
          else cmp = 0;
        }

        if (sortDir === \'desc\') cmp *= -1;
        return cmp;
      });

      $.each(rows, function (idx, row) {
        $tbody.append(row);
      });

      console.log(\'[marks-sort] sorting complete\');
    }

    applyMarksFilterCounts();
    sortMarksTable();
    filterMarksTable();

    $(\'#marks-filter\').on(\'change\', function () {
      console.log(\'[marks-filter] #marks-filter changed to\', this.value);
      filterMarksTable();
    });

    $(\'#marks-sortby, #marks-sortdir\').on(\'change\', function () {
      console.log(\'[marks-sort] sort controls changed: sortBy=\', $(\'#marks-sortby\').val(), \', sortDir=\', $(\'#marks-sortdir\').val());
      sortMarksTable();
      filterMarksTable();
    });

    $(\'#clear-marks-filters\').on(\'click\', function () {
      console.log(\'[marks-filter] #clear-marks-filters clicked\');
      $(\'#marks-filter\').val(\'all\');
      $(\'#marks-sortby\').val(\'name\');
      $(\'#marks-sortdir\').val(\'desc\');
      sortMarksTable();
      filterMarksTable();
    });
  });
</script>';


// seaarching the table scriptt
      echo '<script>
        // Keep track of last search term so we know when user cleared the field
        var lastTableSearchTerm = \'\';

        // Global search function used by onkeyup="filterTable(this)"
        function filterTable(inputEl) {
          var $input = $(inputEl);
          var term = $input.val().toLowerCase().trim();
          var tableSelector = $input.data(\'target\');

          console.log(\'[table-search] raw data-target =\', tableSelector);

          // Fallback if data-target is missing or just "#"
          if (!tableSelector || tableSelector === \'#\') {
            tableSelector = \'#marks_capture_table\';
            console.log(\'[table-search] fallback selector ->\', tableSelector);
          }

          console.log(\'[table-search] term =\', term, \'tableSelector =\', tableSelector);

          var $table = $(tableSelector);
          if (!$table.length) {
            console.log(\'[table-search] ERROR: table not found for selector\', tableSelector);
            return;
          }

          // If search is cleared, restore rows according to current filter
          if (term === \'\') {
            console.log(\'[table-search] search cleared\');
            if (lastTableSearchTerm !== \'\') {
              // Re-apply current filter (if your marks filter bar exists)
              if ($(\'#marks-filter\').length) {
                console.log(\'[table-search] re-triggering #marks-filter change to restore visibility\');
                $(\'#marks-filter\').trigger(\'change\');
              } else {
                console.log(\'[table-search] no #marks-filter, showing all rows\');
                $table.find(\'tbody tr\').show();
              }
            }
            lastTableSearchTerm = \'\';
            return;
          }

          lastTableSearchTerm = term;

          // Apply search on top of current filter state
          $table.find(\'tbody tr\').each(function (idx) {
            var $tr = $(this);

            // Skip rows without cells (just in case)
            if ($tr.find(\'td\').length === 0) {
              return;
            }

            // If row is already hidden by other filters, leave it hidden
            if (!$tr.is(\':visible\')) {
              return;
            }

            var rowText = $tr.text().toLowerCase();
            var match = rowText.indexOf(term) !== -1;

            $tr.toggle(match);
          });
        }
      </script>';


          //code for the x or close the search

          echo `<script>
            // Delegate click for ANY clear button inside .table-search
            $(document).on('click', '.table-search button[title="Clear"]', function () {
              var $wrap  = $(this).closest('.table-search');
              var $input = $wrap.find('input[type="text"]');

              if (!$input.length) {
                console.log('[table-search] clear: no input found in wrapper');
                return;
              }

              console.log('[table-search] clear clicked for input id =', $input.attr('id'));

              // Clear value
              $input.val('');

              // Re-apply filter logic (this will restore rows based on current marks filter)
              filterTable($input[0]);
            });
          </script>
          `;

          //echo to show advanced




          //jquery for populating the statistics or iperfomance tab

          echo `<script>
  console.log('[perf] performance script tag loaded, typeof jQuery =', typeof jQuery);

  (function ($) {
    console.log('[perf] IIFE START, $ type =', typeof $);

    // --------------------------------------------------------------
    // 1) Update Performance Tab stats from window.marksPerfStats
    // --------------------------------------------------------------
    function updatePerformanceTabFromStats() {
      console.log('[perf] updatePerformanceTabFromStats() called');

      if (!window.marksPerfStats) {
        console.log('[perf] No stats found on window.marksPerfStats');
        return;
      }

      var s = window.marksPerfStats;
      console.log('[perf] marksPerfStats =', s);

      if ($('#perf-total-students').length) {
        $('#perf-total-students').text(s.totalStudents || '--');
      }

      if ($('#perf-posted-count').length) {
        $('#perf-posted-count').text(s.postedCount || 0);
      }

      if ($('#perf-pass-rate').length) {
        $('#perf-pass-rate').text(s.passRate != null ? s.passRate + '%' : '--%');
      }

      if ($('#perf-fail-rate').length) {
        $('#perf-fail-rate').text(s.failRate != null ? s.failRate + '%' : '--%');
      }

      if ($('#perf-highest-mark').length) {
        $('#perf-highest-mark').text(s.highestMark != null ? s.highestMark : '--');
      }

      if ($('#perf-highest-student').length) {
        $('#perf-highest-student').text(
          s.highestStudent ? 'Student: ' + s.highestStudent : 'Student: --'
        );
      }

      if ($('#perf-lowest-mark').length) {
        $('#perf-lowest-mark').text(s.lowestMark != null ? s.lowestMark : '--');
      }

      if ($('#perf-lowest-student').length) {
        $('#perf-lowest-student').text(
          s.lowestStudent ? 'Student: ' + s.lowestStudent : 'Student: --'
        );
      }

      if ($('#perf-class-average').length) {
        $('#perf-class-average').text(
          s.classAverage != null ? s.classAverage : '--'
        );
      }

      // Extra captured vs no-marks line if you add an element with this id
      if ($('#perf-captured-extra').length) {
        var captured = s.capturedCount || 0;
        var noMarks  = s.noMarksCount || 0;
        $('#perf-captured-extra').text('Captured: ' + captured + ' • No Marks: ' + noMarks);
      }
    }

    // --------------------------------------------------------------
    // 2) NAVISION-style Compare Students picker
    // --------------------------------------------------------------
    var currentCompareSlot = null; // "student1" or "student2"

    function ensureStudentPicker() {
      console.log('[perf] ensureStudentPicker() called');

      if ($('#perf-student-picker').length) {
        console.log('[perf] picker already in DOM');
        return;
      }

      var pickerHtml = [
        '<div id="perf-student-picker" style="position:fixed; inset:0; background:rgba(0,0,0,.3); z-index:1050; display:none;">',
        '  <div style="max-width:520px; margin:60px auto; background:#fff; border-radius:10px; box-shadow:0 10px 25px rgba(15,23,42,.25); font-family:Roboto,system-ui,-apple-system,\'Segoe UI\',Arial,sans-serif;">',
        '    <div style="padding:8px 12px; border-bottom:1px solid #e5e7eb; display:flex; justify-content:space-between; align-items:center;">',
        '      <div style="font-size:13px; font-weight:600;">Select Student</div>',
        '      <button type="button" id="perf-picker-close" style="background:transparent;border:0;font-size:18px;line-height:1;cursor:pointer;">&times;</button>',
        '    </div>',
        '    <div style="padding:8px 12px;">',
        '      <input type="text" id="perf-picker-search" placeholder="Search by reg / name…" style="width:100%; padding:6px 8px; font-size:12px; border:1px solid #e5e7eb; border-radius:6px; margin-bottom:8px;">',
        '      <div style="max-height:260px; overflow:auto; border:1px solid #e5e7eb; border-radius:6px;">',
        '        <table style="width:100%; border-collapse:collapse; font-size:11px;">',
        '          <thead style="position:sticky; top:0; background:#f9fafb; border-bottom:1px solid #e5e7eb;">',
        '            <tr>',
        '              <th style="padding:4px 6px; text-align:left;">Reg Number</th>',
        '              <th style="padding:4px 6px; text-align:left;">Name</th>',
        '              <th style="padding:4px 6px; text-align:left;">Surname</th>',
        '              <th style="padding:4px 6px; text-align:right;">OM%</th>',
        '            </tr>',
        '          </thead>',
        '          <tbody id="perf-picker-tbody"></tbody>',
        '        </table>',
        '      </div>',
        '    </div>',
        '  </div>',
        '</div>'
      ].join('');

      $('body').append(pickerHtml);
      console.log('[perf] picker appended to body');
    }

    function openStudentPicker(slot) {
      console.log('[perf] openStudentPicker for slot =', slot);
      currentCompareSlot = slot;
      ensureStudentPicker();

      var $overlay = $('#perf-student-picker');
      var $tbody   = $('#perf-picker-tbody');
      var list     = window.marksPerfStudents || [];

      console.log('[perf] marksPerfStudents length =', list.length);

      $tbody.empty();
      $.each(list, function (idx, s) {
        var labelName = (s.name || '') + ' ' + (s.surname || '');
        var $row = $('<tr>')
          .css({ cursor: 'pointer' })
          .attr('data-reg', s.reg)
          .attr('data-name', labelName)
          .append($('<td>').css({ padding: '4px 6px' }).text(s.reg))
          .append($('<td>').css({ padding: '4px 6px' }).text(s.name))
          .append($('<td>').css({ padding: '4px 6px' }).text(s.surname))
          .append($('<td>').css({ padding: '4px 6px', textAlign: 'right' }).text(s.overall !== null ? s.overall : '--'));

        $tbody.append($row);
      });

      $('#perf-picker-search').val('');
      $overlay.show();
      console.log('[perf] picker overlay shown');
    }

    function closeStudentPicker() {
      console.log('[perf] closeStudentPicker()');
      $('#perf-student-picker').hide();
      currentCompareSlot = null;
    }

    // Search in picker
    $(document).on('keyup', '#perf-picker-search', function () {
      var term = $(this).val().toLowerCase();
      console.log('[perf] picker search term =', term);
      $('#perf-picker-tbody tr').each(function () {
        var txt = $(this).text().toLowerCase();
        $(this).toggle(txt.indexOf(term) !== -1);
      });
    });

    // Select row in picker
    $(document).on('click', '#perf-picker-tbody tr', function () {
      console.log('[perf] picker row clicked, currentCompareSlot =', currentCompareSlot);
      if (!currentCompareSlot) return;

      var reg   = $(this).data('reg');
      var label = $(this).data('name');

      console.log('[perf] chosen reg =', reg, 'label =', label);

      if (currentCompareSlot === 'student1') {
        $('.perf-compare-slot').first().find('.perf-compare-label')
          .text(reg + ' - ' + label);
        $('#perf-add-student1 i')
          .removeClass('ri-user-add-line')
          .addClass('ri-user-line');
      } else if (currentCompareSlot === 'student2') {
        $('.perf-compare-slot').last().find('.perf-compare-label')
          .text(reg + ' - ' + label);
        $('#perf-add-student2 i')
          .removeClass('ri-user-add-line')
          .addClass('ri-user-line');
      }

      closeStudentPicker();
    });

    // Close picker
    $(document).on('click', '#perf-picker-close', function () {
      console.log('[perf] picker close clicked');
      closeStudentPicker();
    });

    // Click outside modal to close
    $(document).on('click', '#perf-student-picker', function (e) {
      if (e.target.id === 'perf-student-picker') {
        console.log('[perf] overlay background clicked');
        closeStudentPicker();
      }
    });

    // Open picker from buttons
    $(document).on('click', '#perf-add-student1', function () {
      console.log('[perf] add student1 clicked');
      openStudentPicker('student1');
    });

    $(document).on('click', '#perf-add-student2', function () {
      console.log('[perf] add student2 clicked');
      openStudentPicker('student2');
    });

    // Compare button (stub)
    $(document).on('click', '#perf-compare-btn', function () {
      console.log('[perf] Compare Students button clicked – wire real comparison later.');
    });

    // --------------------------------------------------------------
    // 3) Init – THIS is what "triggers" the whole thing
    // --------------------------------------------------------------
    $(function () {
      console.log('[perf] document ready inside IIFE');
      console.log('[perf] window.marksPerfStats on ready =', window.marksPerfStats);
      console.log('[perf] window.marksPerfStudents on ready =', window.marksPerfStudents);

      // Fill stats once on page load
      updatePerformanceTabFromStats();

      // Also re-fill whenever the Performance tab is activated
      $(document).on('shown.bs.tab', 'a[href="#tab-performance"]', function (e) {
        console.log('[perf] shown.bs.tab fired for #tab-performance; event target =', e.target);
        updatePerformanceTabFromStats();
      });
    });

  })(jQuery);
</script>


`;
       


                   echo' </div>
							
						</div>
			
						</div>
		</div>
    




<!-- ===== PERFORMANCE TAB (START) ===== -->

<div class="tab-pane fade" id="tab-performance" role="tabpanel" aria-labelledby="marks-tab-performance">
  <!-- PERFORMANCE DASHBOARD STYLES (put once on the page) -->
  <style>
    :root{
      --perf-sky:#0ea5e9;
      --perf-black:#111827;
      --perf-yellow:#fbbf24;
      --perf-grey:#6b7280;
      --perf-card-bg:#ffffff;
      --perf-card-border:#e5e7eb;
      --perf-page-bg:#f3f4f6;
    }

    .perf-page{
      background:#ffffff;
      padding:10px;
      font-family:Roboto,system-ui,-apple-system,"Segoe UI",Arial,sans-serif;
    }

    .perf-card{
      background:var(--perf-card-bg);
      border:1px solid var(--perf-card-border);
      border-radius:10px;
      padding:14px 16px;
      height:100%;
    }

    .perf-card-header{
      display:flex;
      justify-content:space-between;
      align-items:center;
      margin-bottom:8px;
    }

    .perf-title{
      font-size:14px;
      font-weight:700;
      color:var(--perf-black);
    }

    .perf-sub{
      font-size:12px;
      color:var(--perf-grey);
    }

    .perf-donut-wrap{
      display:flex;
      align-items:center;
      gap:16px;
    }
    .perf-donut{
      width:150px;
      height:150px;
      border-radius:50%;
      border:12px solid #022c44;
      border-top-color:#f97316;
      border-right-color:#22c55e;
      border-bottom-color:#0ea5e9;
      position:relative;
    }
    .perf-donut::after{
      content:\'\';
      position:absolute;
      inset:18px;
      border-radius:50%;
      background:#ffffff;
    }
    .perf-legend{
      flex:1;
      font-size:12px;
    }
    .perf-legend-item{
      display:flex;
      align-items:center;
      gap:6px;
      margin-bottom:4px;
    }
    .perf-dot{
      width:8px;
      height:8px;
      border-radius:50%;
    }

    .perf-summary-row{
      display:grid;
      grid-template-columns:repeat(2,minmax(0,1fr));
      gap:6px;
      margin-top:6px;
    }
    .perf-summary-item{
      font-size:12px;
      display:flex;
      flex-direction:column;
    }
    .perf-summary-label{
      color:var(--perf-grey);
    }
    .perf-summary-value{
      font-weight:700;
      color:var(--perf-black);
      font-size:15px;
    }

    .perf-bottom-row{margin-top:12px;}

    .perf-stat-card{
      background:var(--perf-card-bg);
      border:1px solid var(--perf-card-border);
      border-radius:10px;
      padding:10px 12px;
      height:100%;
      display:flex;
      flex-direction:column;
      justify-content:space-between;
    }

    .perf-stat-section{
      display:flex;
      align-items:flex-start;
      justify-content:space-between;
      margin-bottom:6px;
    }

    .perf-stat-icon{
      width:26px;
      height:26px;
      border-radius:8px;
      display:flex;
      align-items:center;
      justify-content:center;
      color:#fff;
      font-size:15px;
    }
    .perf-stat-value{
      font-size:18px;
      font-weight:700;
      color:var(--perf-black);
      margin-top:2px;
    }
    .perf-stat-sub{
      font-size:11px;
      color:var(--perf-grey);
    }

    .bg-sky{background:var(--perf-sky);}
    .bg-black{background:var(--perf-black);}
    .bg-yellow{background:var(--perf-yellow); color:#1f2933;}
    .bg-grey{background:#9ca3af;}

    /* Bigger compare button, keep theme, force Roboto */
    .perf-compare-btn{
      font-family:Roboto,system-ui,-apple-system,"Segoe UI",Arial,sans-serif;
      font-size:13.5px;
      font-weight:700;
      padding:6px 14px;
      border-radius:999px;
    }

    /* Compare slots (add student 1 / 2) */
    .perf-compare-wrapper{
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:18px;
      margin-top:6px;
      margin-bottom:8px;
    }
    .perf-compare-slot{
      flex:1;
      text-align:center;
      font-family:Roboto,system-ui,-apple-system,"Segoe UI",Arial,sans-serif;
    }
    .perf-compare-add{
      width:46px;
      height:46px;
      border-radius:50%;
      border:0;
      background:#f3f4f6;
      display:flex;
      align-items:center;
      justify-content:center;
      cursor:pointer;
      color:var(--perf-black);
    }
    .perf-compare-add i{
      font-size:22px;
    }
    .perf-compare-label{
      margin-top:6px;
      font-size:12px;
      font-weight:600;
      color:#4b5563;
    }
    .perf-compare-vs{
      font-weight:700;
      font-size:13px;
      color:var(--perf-black);
      white-space:nowrap;
    }
  </style>

  <div class="perf-page">

    <!-- INTRO BAR -->
    <br>
    <div style="padding:8px 10px; background-color:#ffffff; border-bottom:1px solid #dee2e6;">
      <div style="font-family:\'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif; font-size:13px; color:#333; font-weight:600;">
        Course / Student Performance
      </div>
      <div style="font-family:\'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif; font-size:9px; color:#666;">
        Overview of highest, lowest, class average and student comparisons.
      </div>
      <div>
        <div style="float:right; display:flex; align-items:center; gap:0;">
          <!-- keep empty or add small tools later -->
        </div>
      </div>
    </div>

    <div class="row mt-2">
      <!-- DONUT / DISTRIBUTION -->
      <div class="col-lg-6 mb-3">
        <div class="perf-card">
          <div class="perf-card-header">
            <div>
              <div class="perf-title">Grade Distribution</div>
              <div class="perf-sub">Class spread for this course</div>
            </div>
          </div>

         <div class="perf-donut-wrap">
            <!-- Chart area (bar + normal line) -->
            <div style="flex:1; min-height:180px;">
              <canvas id="perf-grade-bar-canvas"></canvas>
            </div>

            <!-- Legend will be built dynamically -->
            <div class="perf-legend" id="perf-legend-grades">
              <div class="perf-sub" style="margin-top:6px;">
                Grade distribution (bars = actual; line = normal curve over grade positions).
              </div>
            </div>
          </div>


        </div>
      </div>

      <!-- SUMMARY -->
      <div class="col-lg-6 mb-3">
        <div class="perf-card">
          <div class="perf-card-header">
            <div class="perf-title">Result Summary</div>
          </div>

          <div class="perf-summary-row">
            <div class="perf-summary-item">
              <span class="perf-summary-label">Total Students</span>
              <span class="perf-summary-value" id="perf-total-students">--</span>
            </div>
            <div class="perf-summary-item">
              <span class="perf-summary-label">Posted Marks</span>
              <span class="perf-summary-value" id="perf-posted-count">--</span>
            </div>
            <div class="perf-summary-item">
              <span class="perf-summary-label">Pass Rate</span>
              <span class="perf-summary-value" id="perf-pass-rate">--%</span>
            </div>
            <div class="perf-summary-item">
              <span class="perf-summary-label">Failure Rate</span>
              <span class="perf-summary-value" id="perf-fail-rate">--%</span>
            </div>
            <div class="perf-summary-item">
              <span class="perf-summary-label">Captured</span>
              <span class="perf-summary-value" id="perf-captured-count">--</span>
            </div>
            <div class="perf-summary-item">
              <span class="perf-summary-label">No Marks</span>
              <span class="perf-summary-value" id="perf-no-marks-count">--</span>
            </div>
          </div>

<div class="perf-sub" style="margin-top:10px;">
  Based on current marks in the system for this course.
</div>

        
        </div>
      </div>
    </div>




   <!-- NEW: STUDENT LEADERBOARD CHART (Full Width) -->
    <div class="row mb-3">
      <div class="col-12">
        <div class="perf-card">
          <div class="perf-card-header">
            <div>
              <div class="perf-title">Student Leaderboard (OM%)</div>
              <div class="perf-sub">Ranked by Overall Mark</div>
            </div>
          </div>
          <!-- Dynamic height container based on number of students -->
          <div id="perf-ranking-container" style="position:relative; width:100%; min-height:300px;">
            <canvas id="perf-student-ranking-canvas"></canvas>
          </div>
        </div>
      </div>
    </div>

<!-- COMPARATIVE PERFORMANCE ROW 1: GLOBAL CONTEXT -->
<div class="row mb-3">
  <div class="col-lg-6 col-md-12 mb-3">
    <div class="perf-card">
      <div class="perf-card-header">
        <div>
          <div class="perf-title">Course Difficulty Context</div>
          <div class="perf-sub">This Course Average vs. Combined Average of all other courses.</div>
        </div>
      </div>
      <!-- Fixed height for the Global Comparison -->
      <div style="position:relative; width:100%; height:300px; display:flex; justify-content:center;">
        <canvas id="perf-global-comparison-canvas"></canvas>
      </div>
    </div>
  </div>
</div>



<!-- COMPARATIVE PERFORMANCE ROW 2: INDIVIDUAL STUDENT CONTEXT (FULL WIDTH) -->
<div class="row mb-3">
  <div class="col-12 mb-3">
    <div class="perf-card">
      <div class="perf-card-header">
        <div>
          <div class="perf-title">Student Performance Context</div>
          <div class="perf-sub">
            Comparing each student\'s <b>Course Mark</b> (Blue) vs. <b>Average in Other Subjects</b> (Orange).
          </div>
        </div>
      </div>

      <!-- SCROLLABLE CONTAINER (taller, cleaner) -->
      <div
        id="perf-student-context-scroll"
        style="position:relative;width:100%;height:720px;overflow-y:auto;overflow-x:hidden;
               padding-right:18px;border-top:1px solid #f3f4f6;"
      >
        <canvas id="perf-student-context-canvas" style="display:block;width:100%;"></canvas>
      </div>

    </div>
  </div>
</div>





    <!-- BOTTOM STATS -->
    <div class="row perf-bottom-row">
      <!-- COMBINED CARD: Highest, Lowest, Average -->
      <div class="col-md-6 col-sm-12 mb-3">
        <div class="perf-stat-card">
          <!-- Highest -->
          <div class="perf-stat-section">
            <div>
              <div class="staffbar-label" style="font-size:11px; gap:4px;">
                <i class="ri-arrow-up-line"></i>
                Highest Mark
              </div>
              <div class="perf-stat-value" id="perf-highest-mark">--</div>
              <div class="perf-stat-sub" id="perf-highest-student">Student: --</div>
            </div>
            <div class="perf-stat-icon bg-sky">
              <i class="ri-bar-chart-2-line"></i>
            </div>
          </div>

          <hr style="margin:6px 0; border-top:1px solid #e5e7eb;">

          <!-- Lowest -->
          <div class="perf-stat-section">
            <div>
              <div class="staffbar-label" style="font-size:11px; gap:4px;">
                <i class="ri-arrow-down-line"></i>
                Lowest Mark
              </div>
              <div class="perf-stat-value" id="perf-lowest-mark">--</div>
              <div class="perf-stat-sub" id="perf-lowest-student">Student: --</div>
            </div>
            <div class="perf-stat-icon bg-black">
              <i class="ri-bar-chart-horizontal-line"></i>
            </div>
          </div>

          <hr style="margin:6px 0; border-top:1px solid #e5e7eb;">

          <!-- Average -->
          <div class="perf-stat-section">
            <div>
              <div class="staffbar-label" style="font-size:11px; gap:4px;">
                <i class="ri-line-chart-line"></i>
                Class Average
              </div>
              <div class="perf-stat-value" id="perf-class-average">--</div>
              <div class="perf-stat-sub">Average of all posted marks</div>
            </div>
            <div class="perf-stat-icon bg-yellow">
              <i class="ri-pie-chart-2-line"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- COMPARE STUDENTS CARD -->
      <div class="col-md-6 col-sm-12 mb-3">
        <div class="perf-stat-card">
          <div class="perf-stat-section">
            <div>
              <div class="staffbar-label" style="font-size:11px; gap:4px;">
                <i class="ri-user-2-line"></i>
                Compare Students
              </div>
              <div class="perf-stat-sub">
                Select two students to compare against the class.
              </div>
            </div>
            <div class="perf-stat-icon bg-grey">
              <i class="ri-user-star-line"></i>
            </div>
          </div>

          <!-- Add Student 1 / VS / Student 2 -->
          <div class="perf-compare-wrapper">
            <div class="perf-compare-slot">
              <button type="button" id="perf-add-student1" class="perf-compare-add">
                <i class="ri-user-add-line"></i>
              </button>
              <div class="perf-compare-label">Click + to add Student 1</div>
            </div>

            <div class="perf-compare-vs">VS</div>

            <div class="perf-compare-slot">
              <button type="button" id="perf-add-student2" class="perf-compare-add">
                <i class="ri-user-add-line"></i>
              </button>
              <div class="perf-compare-label">Click + to add Student 2</div>
            </div>
          </div>

          <div style="margin-top:8px;">
            <!-- uses SAME button theme as Advanced / Clear Filters, but bigger -->
            <button type="button" id="perf-compare-btn" class="staffbar-btn perf-compare-btn">
              <i class="ri-equalizer-line"></i>
              Compare Students
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>


<!-- ===== PERFORMANCE TAB (END) ===== -->';

echo '<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>';

echo <<<'PERFJS'
<script>
  console.log('[perf] performance script loaded, typeof jQuery =', typeof jQuery);

  (function ($) {
    console.log('[perf] IIFE START, $ type =', typeof $);

    // Global chart instances
    window.perfGradeChart = window.perfGradeChart || null;
    window.perfRankingChart = window.perfRankingChart || null;

    // =========================================================
    // 1. DATA EXTRACTION HELPERS
    // =========================================================

    function computeGradeDistributionFromTable() {
      var $table = $('#marks_capture_table');
      if (!$table.length) return null;

      var gradeColIndex = -1;
      // Find grade column
      $table.find('thead tr').first().find('th').each(function (idx) {
        var id = $(this).attr('id');
        var text = $(this).text().toLowerCase().trim();
        if (id === 'grade' || text === 'grade') gradeColIndex = idx;
      });

      if (gradeColIndex === -1) return null;

      var gradeCounts = {};
      var $rows = $table.find('tbody tr:visible'); 

      $rows.each(function () {
        var $cells = $(this).children('td');
        if ($cells.length <= gradeColIndex) return;
        var grade = ($cells.eq(gradeColIndex).text() || '').toString().trim();
        if (!grade) return;
        grade = grade.replace(/\s+/g, ' ').trim();
        
        if (!gradeCounts[grade]) gradeCounts[grade] = 0;
        gradeCounts[grade]++;
      });

      return gradeCounts;
    }

	
    // UPDATED: Fetches Name, Surname and Reg Number
    function computeStudentMarksFromTable() {
      var $table = $('#marks_capture_table');
      if (!$table.length) return null;

      var omColIndex = -1;
    

      var regColIndex = 1; 
      var nameColIndex = 2;
      var surnameColIndex = 3;

      // Find OM% column dynamically to be safe
      $table.find('thead tr').first().find('th').each(function (idx) {
        var id = $(this).attr('id');
        var text = $(this).text().toLowerCase().trim();
        if (id === 'mark' || text === 'om%' || text === 'mark') omColIndex = idx;
      });

      if (omColIndex === -1) return null;

      var studentData = [];
      var $rows = $table.find('tbody tr:visible');

      $rows.each(function () {
        var $cells = $(this).children('td');
        
        // Grab text from specific columns
        var reg = $cells.eq(regColIndex).text().trim();
        var name = $cells.eq(nameColIndex).text().trim();
        var surname = $cells.eq(surnameColIndex).text().trim();
        var rawMark = $cells.eq(omColIndex).text().trim();
        var mark = parseFloat(rawMark);

        // Only add if mark is a valid number
        if (reg && !isNaN(mark)) {
          // Format label: "Name Surname [RegNumber]"
          var fullLabel = name + ' ' + surname + ' [' + reg + ']';
          studentData.push({ label: fullLabel, value: mark });
        }
      });

      return studentData;
    }



    function updateGradeBarChartFromTable() {
      if (typeof Chart === 'undefined') return;

      var counts = computeGradeDistributionFromTable();
      if (!counts) return;

      var labels = Object.keys(counts);
      var values = labels.map(function(k) { return counts[k]; });
      
      // Calculate Normal Curve data
      var total = values.reduce((a, b) => a + b, 0);
      var indices = Array.from({length: values.length}, (_, i) => i);
      var mean = indices.reduce((sum, i) => sum + (i * values[i]), 0) / (total || 1);
      var variance = indices.reduce((sum, i) => sum + (values[i] * Math.pow(i - mean, 2)), 0) / (total || 1);
      var sigma = Math.sqrt(variance) || 1;
      var maxCount = Math.max(...values);
      
      var normalValues = indices.map(i => {
         var z = (i - mean) / sigma;
         var phi = Math.exp(-0.5 * z * z) / (sigma * Math.sqrt(2 * Math.PI));
         return phi;
      });
      var scale = Math.max(...normalValues) > 0 ? (maxCount / Math.max(...normalValues)) : 1;
      normalValues = normalValues.map(v => v * scale);

      var $canvas = $('#perf-grade-bar-canvas');
      if (!$canvas.length) return;
      var ctx = $canvas[0].getContext('2d');

      var bgColors = labels.map((_, i) => ['#0ea5e9', '#22c55e', '#f97316', '#a855f7', '#eab308'][i % 5]);

      if (window.perfGradeChart) window.perfGradeChart.destroy();

      window.perfGradeChart = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: labels,
          datasets: [
            { type: 'bar', label: 'Count', data: values, backgroundColor: bgColors, borderRadius: 2 },
            { type: 'line', label: 'Curve', data: normalValues, borderColor: '#111827', borderWidth: 2, tension: 0.4, pointRadius: 0 }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { display: false } },
          scales: {
            y: { beginAtZero: true, grid: { display: true, drawBorder: false } },
            x: { grid: { display: false } }
          }
        }
      });
      
       var $legend = $('#perf-legend-grades');
       $legend.empty();
       labels.forEach((lbl, i) => {
         var pct = Math.round((values[i]/total)*100);
         $legend.append('<div class="perf-legend-item"><span class="perf-dot" style="background:'+bgColors[i]+'"></span><span>'+lbl+' ('+values[i]+', '+pct+'%)</span></div>');
       });
    }

	

    function updateStudentRankingChart() {
      if (typeof Chart === 'undefined') return;

      var students = computeStudentMarksFromTable();
      if (!students || students.length === 0) return;

      // 1. Calculate Class Average
      var totalMarks = students.reduce((sum, s) => sum + s.value, 0);
      var classAverage = Math.round(totalMarks / students.length);

      // 2. Sort Descending (Highest Mark Top)
      students.sort(function(a, b) { return b.value - a.value; });

      var labels = students.map(s => s.label);
      var values = students.map(s => s.value);

      // 3. Dynamic Colors
      // Green if >= Average, Dark Blue if < Average
      var bgColors = values.map((val) => {
        if (val >= classAverage) return '#22c55e'; 
        return '#002060';
      });

      // 4. Dynamic Height
      var dynamicHeight = (students.length * 30) + 60;
      $('#perf-ranking-container').css('height', dynamicHeight + 'px');

      var $canvas = $('#perf-student-ranking-canvas');
      if (!$canvas.length) return;
      var ctx = $canvas[0].getContext('2d');

      if (window.perfRankingChart) window.perfRankingChart.destroy();

      window.perfRankingChart = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: labels,
          datasets: [{
            label: 'Mark',
            data: values,
            backgroundColor: bgColors,
            barPercentage: 0.8,
            categoryPercentage: 0.9,
            borderRadius: 2
          }]
        },
        options: {
          indexAxis: 'y', // Horizontal
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { display: false },
            tooltip: { 
              callbacks: { label: function(c) { return 'OM%: ' + c.raw; } } 
            }
          },
          scales: {
            x: { 
              display: true, 
              max: 100,
              grid: { color: '#f3f4f6' }
            },
            y: { 
              grid: { display: false, drawBorder: false },
              ticks: { 
                font: { family: 'Roboto', size: 11, weight: 'bold' },
                color: '#374151',
                autoSkip: false
              }
            }
          }
        },
        plugins: [
          // Plugin 1: White numbers inside bar
          {
            id: 'leaderboardLabels',
            afterDatasetsDraw: function(chart) {
              var ctx = chart.ctx;
              chart.data.datasets.forEach(function(dataset, i) {
                var meta = chart.getDatasetMeta(i);
                meta.data.forEach(function(bar, index) {
                  var value = dataset.data[index];
                  if(value > 0){
                     ctx.fillStyle = '#ffffff';
                     ctx.font = 'bold 11px Roboto';
                     ctx.textAlign = 'right';
                     ctx.textBaseline = 'middle';
                     ctx.fillText(value, bar.x - 6, bar.y);
                  }
                });
              });
            }
          },
          // Plugin 2: Average Line
          {
            id: 'averageLine',
            afterDraw: function(chart) {
              var ctx = chart.ctx;
              var xAxis = chart.scales.x;
              var yAxis = chart.scales.y;
              var xPos = xAxis.getPixelForValue(classAverage);
              
              if (xPos) {
                ctx.save();
                ctx.beginPath();
                ctx.strokeStyle = '#dc2626'; // Red
                ctx.lineWidth = 2;
                ctx.setLineDash([5, 5]);
                ctx.moveTo(xPos, yAxis.top);
                ctx.lineTo(xPos, yAxis.bottom);
                ctx.stroke();
                
                // Text Label
                ctx.fillStyle = '#dc2626';
                ctx.font = 'bold 10px Roboto';
                ctx.textAlign = 'center';
                ctx.fillText('Avg: ' + classAverage + '%', xPos, yAxis.top - 6);
                ctx.restore();
              }
            }
          }
        ]
      });
    }

	


    function updatePerformanceTabFromStats() {
      if (window.marksPerfStats) {
        var s = window.marksPerfStats;
        if ($('#perf-total-students').length) $('#perf-total-students').text(s.totalStudents || '--');
        if ($('#perf-posted-count').length) $('#perf-posted-count').text(s.postedCount || 0);
        if ($('#perf-pass-rate').length) $('#perf-pass-rate').text(s.passRate != null ? s.passRate + '%' : '--%');
        if ($('#perf-fail-rate').length) $('#perf-fail-rate').text(s.failRate != null ? s.failRate + '%' : '--%');
        if ($('#perf-highest-mark').length) $('#perf-highest-mark').text(s.highestMark != null ? s.highestMark : '--');
        if ($('#perf-highest-student').length) $('#perf-highest-student').text(s.highestStudent ? 'Student: ' + s.highestStudent : 'Student: --');
        if ($('#perf-lowest-mark').length) $('#perf-lowest-mark').text(s.lowestMark != null ? s.lowestMark : '--');
        if ($('#perf-lowest-student').length) $('#perf-lowest-student').text(s.lowestStudent ? 'Student: ' + s.lowestStudent : 'Student: --');
        if ($('#perf-class-average').length) $('#perf-class-average').text(s.classAverage != null ? s.classAverage : '--');
        if ($('#perf-captured-count').length) $('#perf-captured-count').text(s.capturedCount != null ? s.capturedCount : '--');
        if ($('#perf-no-marks-count').length) $('#perf-no-marks-count').text(s.noMarksCount != null ? s.noMarksCount : '--');
      }

      updateGradeBarChartFromTable();
      updateStudentRankingChart(); 
    }

    // Picker Logic
    var currentCompareSlot = null;
    function ensureStudentPicker() {
      if ($('#perf-student-picker').length) return;
      var pickerHtml = '<div id="perf-student-picker" style="position:fixed; inset:0; background:rgba(0,0,0,.3); z-index:1050; display:none;">' +
        '<div style="max-width:520px; margin:60px auto; background:#fff; border-radius:10px; box-shadow:0 10px 25px rgba(15,23,42,.25); font-family:Roboto,sans-serif;">' +
          '<div style="padding:8px 12px; border-bottom:1px solid #e5e7eb; display:flex; justify-content:space-between; align-items:center;">' +
            '<div style="font-size:13px; font-weight:600;">Select Student</div>' +
            '<button type="button" id="perf-picker-close" style="background:transparent;border:0;font-size:18px;line-height:1;cursor:pointer;">&times;</button>' +
          '</div>' +
          '<div style="padding:8px 12px;">' +
            '<input type="text" id="perf-picker-search" placeholder="Search..." style="width:100%; padding:6px 8px; font-size:12px; border:1px solid #e5e7eb; border-radius:6px; margin-bottom:8px;">' +
            '<div style="max-height:260px; overflow:auto; border:1px solid #e5e7eb; border-radius:6px;">' +
              '<table style="width:100%; border-collapse:collapse; font-size:11px;">' +
                '<tbody id="perf-picker-tbody"></tbody>' +
              '</table>' +
            '</div>' +
          '</div>' +
        '</div>' +
      '</div>';
      $('body').append(pickerHtml);
    }
    function openStudentPicker(slot) {
      currentCompareSlot = slot;
      ensureStudentPicker();
      var $tbody = $('#perf-picker-tbody');
      var list = window.marksPerfStudents || [];
      $tbody.empty();
      $.each(list, function (idx, s) {
        var labelName = (s.name || '') + ' ' + (s.surname || '');
        var $row = $('<tr>').css({ cursor: 'pointer', borderBottom: '1px solid #f3f4f6' })
          .attr('data-reg', s.reg).attr('data-name', labelName)
          .append($('<td>').css({ padding: '6px' }).text(s.reg))
          .append($('<td>').css({ padding: '6px' }).text(labelName))
          .append($('<td>').css({ padding: '6px', textAlign: 'right', fontWeight: 'bold' }).text(s.overall !== null ? s.overall : '--'));
        $tbody.append($row);
      });
      $('#perf-picker-search').val('');
      $('#perf-student-picker').show();
    }
    function closeStudentPicker() { $('#perf-student-picker').hide(); currentCompareSlot = null; }
    $(document).on('keyup', '#perf-picker-search', function () {
      var term = $(this).val().toLowerCase();
      $('#perf-picker-tbody tr').each(function () {
        $(this).toggle($(this).text().toLowerCase().indexOf(term) !== -1);
      });
    });
    $(document).on('click', '#perf-picker-tbody tr', function () {
      if (!currentCompareSlot) return;
      var reg = $(this).data('reg');
      var label = $(this).data('name');
      if (currentCompareSlot === 'student1') {
        $('.perf-compare-slot').first().find('.perf-compare-label').text(reg + ' - ' + label);
      } else if (currentCompareSlot === 'student2') {
        $('.perf-compare-slot').last().find('.perf-compare-label').text(reg + ' - ' + label);
      }
      closeStudentPicker();
    });
    $(document).on('click', '#perf-picker-close, #perf-student-picker', function (e) {
      if(e.target === this) closeStudentPicker();
    });
    $(document).on('click', '#perf-add-student1', function () { openStudentPicker('student1'); });
    $(document).on('click', '#perf-add-student2', function () { openStudentPicker('student2'); });

    $(function () {
      updatePerformanceTabFromStats();
      $(document).on('shown.bs.tab', 'a[href="#tab-performance"]', function () {
        updatePerformanceTabFromStats();
      });
      $(document).on('click', '#marks-tab-performance', function () {
        setTimeout(function () { updatePerformanceTabFromStats(); }, 50);
      });
      $('#marks-filter').on('change', function() {
         setTimeout(function() {
             updateGradeBarChartFromTable();
             updateStudentRankingChart();
         }, 200);
      });
    });

  })(jQuery);
</script>
PERFJS;

echo '<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>';




echo '<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>';

echo '
<!-- ===== PERFORMANCE TAB (START) ===== -->
<div class="tab-pane fade" id="tab-performance" role="tabpanel" aria-labelledby="marks-tab-performance">
  <style>
    :root{ --perf-sky:#0ea5e9; --perf-black:#111827; --perf-yellow:#fbbf24; --perf-grey:#6b7280; --perf-card-bg:#ffffff; --perf-card-border:#e5e7eb; --perf-page-bg:#f3f4f6; }
    .perf-page{ background:#ffffff; padding:10px; font-family:Roboto,system-ui,-apple-system,"Segoe UI",Arial,sans-serif; }
    .perf-card{ background:var(--perf-card-bg); border:1px solid var(--perf-card-border); border-radius:10px; padding:14px 16px; height:100%; }
    .perf-card-header{ display:flex; justify-content:space-between; align-items:center; margin-bottom:8px; }
    .perf-title{ font-size:14px; font-weight:700; color:var(--perf-black); }
    .perf-sub{ font-size:12px; color:var(--perf-grey); }
    .perf-donut-wrap{ display:flex; align-items:center; gap:16px; }
    .perf-legend{ flex:1; font-size:12px; }
    .perf-legend-item{ display:flex; align-items:center; gap:6px; margin-bottom:4px; }
    .perf-dot{ width:8px; height:8px; border-radius:50%; }
    .perf-summary-row{ display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:6px; margin-top:6px; }
    .perf-summary-item{ font-size:12px; display:flex; flex-direction:column; }
    .perf-summary-label{ color:var(--perf-grey); }
    .perf-summary-value{ font-weight:700; color:var(--perf-black); font-size:15px; }
    .perf-bottom-row{ margin-top:12px; }
    .perf-stat-card{ background:var(--perf-card-bg); border:1px solid var(--perf-card-border); border-radius:10px; padding:10px 12px; height:100%; display:flex; flex-direction:column; justify-content:space-between; }
    .perf-stat-section{ display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:6px; }
    .perf-stat-icon{ width:26px; height:26px; border-radius:8px; display:flex; align-items:center; justify-content:center; color:#fff; font-size:15px; }
    .perf-stat-value{ font-size:18px; font-weight:700; color:var(--perf-black); margin-top:2px; }
    .perf-stat-sub{ font-size:11px; color:var(--perf-grey); }
    .bg-sky{ background:var(--perf-sky); } .bg-black{ background:var(--perf-black); } .bg-yellow{ background:var(--perf-yellow); color:#1f2933; } .bg-grey{ background:#9ca3af; }
    .perf-compare-btn{ font-family:Roboto,system-ui,-apple-system,"Segoe UI",Arial,sans-serif; font-size:13.5px; font-weight:700; padding:6px 14px; border-radius:999px; }
    .perf-compare-wrapper{ display:flex; align-items:center; justify-content:space-between; gap:18px; margin-top:6px; margin-bottom:8px; }
    .perf-compare-slot{ flex:1; text-align:center; font-family:Roboto,system-ui,-apple-system,"Segoe UI",Arial,sans-serif; }
    .perf-compare-add{ width:46px; height:46px; border-radius:50%; border:0; background:#f3f4f6; display:flex; align-items:center; justify-content:center; cursor:pointer; color:var(--perf-black); }
    .perf-compare-add i{ font-size:22px; }
    .perf-compare-label{ margin-top:6px; font-size:12px; font-weight:600; color:#4b5563; }
    .perf-compare-vs{ font-weight:700; font-size:13px; color:var(--perf-black); white-space:nowrap; }
  </style>

  <div class="perf-page">
    <br>
    <div style="padding:8px 10px; background-color:#ffffff; border-bottom:1px solid #dee2e6;">
      <div style="font-family:\'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif; font-size:13px; color:#333; font-weight:600;">
        Course / Student Performance
      </div>
      <div style="font-family:\'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif; font-size:9px; color:#666;">
        Overview of highest, lowest, class average and student comparisons.
      </div>
    </div>

    <div class="row mt-2">
      <!-- DONUT / DISTRIBUTION -->
      <div class="col-lg-6 mb-3">
        <div class="perf-card">
          <div class="perf-card-header">
            <div>
              <div class="perf-title">Grade Distribution</div>
              <div class="perf-sub">Class spread for this course</div>
            </div>
          </div>
         <div class="perf-donut-wrap">
            <div style="flex:1; min-height:180px;">
              <canvas id="perf-grade-bar-canvas"></canvas>
            </div>
            <div class="perf-legend" id="perf-legend-grades">
              <div class="perf-sub" style="margin-top:6px;">
                Grade distribution (bars = actual; line = normal curve over grade positions).
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- SUMMARY -->
      <div class="col-lg-6 mb-3">
        <div class="perf-card">
          <div class="perf-card-header">
            <div class="perf-title">Result Summary</div>
          </div>
          <div class="perf-summary-row">
            <div class="perf-summary-item"> <span class="perf-summary-label">Total Students</span> <span class="perf-summary-value" id="perf-total-students">--</span> </div>
            <div class="perf-summary-item"> <span class="perf-summary-label">Posted Marks</span> <span class="perf-summary-value" id="perf-posted-count">--</span> </div>
            <div class="perf-summary-item"> <span class="perf-summary-label">Pass Rate</span> <span class="perf-summary-value" id="perf-pass-rate">--%</span> </div>
            <div class="perf-summary-item"> <span class="perf-summary-label">Failure Rate</span> <span class="perf-summary-value" id="perf-fail-rate">--%</span> </div>
            <div class="perf-summary-item"> <span class="perf-summary-label">Captured</span> <span class="perf-summary-value" id="perf-captured-count">--</span> </div>
            <div class="perf-summary-item"> <span class="perf-summary-label">No Marks</span> <span class="perf-summary-value" id="perf-no-marks-count">--</span> </div>
          </div>
          <div class="perf-sub" style="margin-top:10px;"> Based on current marks in the system for this course. </div>
        </div>
      </div>
    </div>

    <!-- NEW: STUDENT LEADERBOARD CHART (SCROLLABLE & COMPACT) -->
    <div class="row mb-3">
      <div class="col-12">
        <div class="perf-card">
          <div class="perf-card-header">
            <div>
              <div class="perf-title">Student Leaderboard (OM%)</div>
              <div class="perf-sub">Ranked by Overall Mark</div>
            </div>
          </div>
          <!-- UPDATED: Scrollable Container with Fixed Height -->
          <div style="position:relative; width:100%; height:400px; overflow-y:auto; overflow-x:hidden; padding-right:10px;">
            <canvas id="perf-student-ranking-canvas"></canvas>
          </div>
        </div>
      </div>
    </div>

    <!-- COMPARATIVE PERFORMANCE ROW 1: GLOBAL CONTEXT -->
    <div class="row mb-3">
      <div class="col-lg-6 col-md-12 mb-3">
        <div class="perf-card">
          <div class="perf-card-header">
            <div>
              <div class="perf-title">Course Difficulty Context</div>
              <div class="perf-sub">This Course Average vs. Combined Average of all other courses.</div>
            </div>
          </div>
          <div style="position:relative; width:100%; height:300px; display:flex; justify-content:center;">
            <canvas id="perf-global-comparison-canvas"></canvas>
          </div>
        </div>
      </div>
    </div>

    <!-- COMPARATIVE PERFORMANCE ROW 2: INDIVIDUAL STUDENT CONTEXT -->
    <div class="row mb-3">
      <div class="col-12 mb-3">
        <div class="perf-card">
          <div class="perf-card-header">
            <div>
              <div class="perf-title">Student Performance Context</div>
              <div class="perf-sub">Comparing each student\'s <b>Course Mark</b> (Blue) vs. <b>Average in Other Subjects</b> (Orange).</div>
            </div>
          </div>
          <div style="position:relative; width:100%; height:600px; overflow-y:auto; overflow-x:hidden; padding-right:15px; border-top:1px solid #f3f4f6;">
            <canvas id="perf-student-context-canvas" style="display:block; width:100%;"></canvas>
          </div>
        </div>
      </div>
    </div>

    <!-- BOTTOM STATS -->
    <div class="row perf-bottom-row">
      <div class="col-md-6 col-sm-12 mb-3">
        <div class="perf-stat-card">
          <div class="perf-stat-section">
            <div>
              <div class="staffbar-label" style="font-size:11px; gap:4px;"> <i class="ri-arrow-up-line"></i> Highest Mark </div>
              <div class="perf-stat-value" id="perf-highest-mark">--</div>
              <div class="perf-stat-sub" id="perf-highest-student">Student: --</div>
            </div>
            <div class="perf-stat-icon bg-sky"> <i class="ri-bar-chart-2-line"></i> </div>
          </div>
          <hr style="margin:6px 0; border-top:1px solid #e5e7eb;">
          <div class="perf-stat-section">
            <div>
              <div class="staffbar-label" style="font-size:11px; gap:4px;"> <i class="ri-arrow-down-line"></i> Lowest Mark </div>
              <div class="perf-stat-value" id="perf-lowest-mark">--</div>
              <div class="perf-stat-sub" id="perf-lowest-student">Student: --</div>
            </div>
            <div class="perf-stat-icon bg-black"> <i class="ri-bar-chart-horizontal-line"></i> </div>
          </div>
          <hr style="margin:6px 0; border-top:1px solid #e5e7eb;">
          <div class="perf-stat-section">
            <div>
              <div class="staffbar-label" style="font-size:11px; gap:4px;"> <i class="ri-line-chart-line"></i> Class Average </div>
              <div class="perf-stat-value" id="perf-class-average">--</div>
              <div class="perf-stat-sub">Average of all posted marks</div>
            </div>
            <div class="perf-stat-icon bg-yellow"> <i class="ri-pie-chart-2-line"></i> </div>
          </div>
        </div>
      </div>

      <div class="col-md-6 col-sm-12 mb-3">
        <div class="perf-stat-card">
          <div class="perf-stat-section">
            <div>
              <div class="staffbar-label" style="font-size:11px; gap:4px;"> <i class="ri-user-2-line"></i> Compare Students </div>
              <div class="perf-stat-sub"> Select two students to compare against the class. </div>
            </div>
            <div class="perf-stat-icon bg-grey"> <i class="ri-user-star-line"></i> </div>
          </div>
          <div class="perf-compare-wrapper">
            <div class="perf-compare-slot">
              <button type="button" id="perf-add-student1" class="perf-compare-add"> <i class="ri-user-add-line"></i> </button>
              <div class="perf-compare-label">Click + to add Student 1</div>
            </div>
            <div class="perf-compare-vs">VS</div>
            <div class="perf-compare-slot">
              <button type="button" id="perf-add-student2" class="perf-compare-add"> <i class="ri-user-add-line"></i> </button>
              <div class="perf-compare-label">Click + to add Student 2</div>
            </div>
          </div>
          <div style="margin-top:8px;">
            <button type="button" id="perf-compare-btn" class="staffbar-btn perf-compare-btn"> <i class="ri-equalizer-line"></i> Compare Students </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  console.log("[perf] performance script loaded");

  (function ($) {
    console.log("[perf] IIFE START");

    // Global chart instances
    window.perfGradeChart = window.perfGradeChart || null;
    window.perfRankingChart = window.perfRankingChart || null;
    window.perfGlobalCompChart = window.perfGlobalCompChart || null;
    window.perfStudentContextChart = window.perfStudentContextChart || null;

    var hasFetchedCohortData = false;

    // =========================================================
    // 1. DATA EXTRACTION HELPERS
    // =========================================================

    function computeGradeDistributionFromTable() {
      var $table = $(\'#marks_capture_table\');
      if (!$table.length) return null;

      var gradeColIndex = -1;
      $table.find(\'thead tr\').first().find(\'th\').each(function (idx) {
        var id = $(this).attr(\'id\');
        var text = $(this).text().toLowerCase().trim();
        if (id === \'grade\' || text === \'grade\') gradeColIndex = idx;
      });

      if (gradeColIndex === -1) return null;

      var gradeCounts = {};
      var $rows = $table.find(\'tbody tr:visible\'); 

      $rows.each(function () {
        var $cells = $(this).children(\'td\');
        if ($cells.length <= gradeColIndex) return;
        var grade = ($cells.eq(gradeColIndex).text() || \'\').toString().trim();
        if (!grade) return;
        grade = grade.replace(/\s+/g, \' \').trim();
        
        if (!gradeCounts[grade]) gradeCounts[grade] = 0;
        gradeCounts[grade]++;
      });

      return gradeCounts;
    }

    function computeStudentMarksFromTable() {
       var $table = $(\'#marks_capture_table\');
       if (!$table.length) {
           console.warn("[perf] Table #marks_capture_table not found");
           return null;
       }

       var omColIndex = -1;
       $table.find(\'thead tr\').first().find(\'th\').each(function (idx) {
         var $th = $(this);
         var txt = $th.text().toLowerCase().trim();
         var id = $th.attr(\'id\');
         if (id === \'mark\' || txt === \'om%\' || txt === \'om\' || txt === \'mark\' || txt === \'overall\') {
             omColIndex = idx;
         }
       });

       if (omColIndex === -1) {
           console.warn(\'[perf] Could not find OM% column.\');
           return null;
       }

       var studentData = [];

       // Iterate ALL rows to ensure graphs are complete
       $table.find(\'tbody tr\').each(function () {
         var $cells = $(this).children(\'td\');
         if ($cells.length <= omColIndex) return;

         var reg = $cells.eq(1).text().trim(); 
         var name = $cells.eq(2).text().trim(); 
         var surname = $cells.eq(3).text().trim(); 
         var rawMark = $cells.eq(omColIndex).text().trim();
         var mark = parseFloat(rawMark);

         if (reg && reg.length > 0 && !isNaN(mark)) {
           var initial = (name.length > 0) ? name.charAt(0).toUpperCase() : \'\';
           var chartLabel = surname + \' \' + initial + \'. [\' + reg + \']\';

           studentData.push({
               label: chartLabel,
               reg: reg,
               value: mark 
           });
         }
       });

       return studentData;
    }

    // =========================================================
    // 2. SUNDERLAND STYLE CHARTS (Grade Dist & Ranking)
    // =========================================================

    function updateGradeBarChartFromTable() {
      if (typeof Chart === \'undefined\') return;

      var counts = computeGradeDistributionFromTable();
      if (!counts) return;

      var sortedData = Object.keys(counts).map(function(key) {
        return { label: key, value: counts[key] };
      });
      sortedData.sort(function(a, b) { return b.value - a.value; });

      var labels = sortedData.map(function(item) { return item.label; });
      var values = sortedData.map(function(item) { return item.value; });

      var $canvas = $(\'#perf-grade-bar-canvas\');
      if (!$canvas.length) return;
      var ctx = $canvas[0].getContext(\'2d\');

      var backgroundColors = values.map(function(_, index) {
        return index === 0 ? \'#e60000\' : \'#002060\'; 
      });

      if (window.perfGradeChart) window.perfGradeChart.destroy();

      window.perfGradeChart = new Chart(ctx, {
        type: \'bar\',
        data: {
          labels: labels,
          datasets: [{
            label: \'Count\',
            data: values,
            backgroundColor: backgroundColors,
            borderColor: \'transparent\',
            borderWidth: 0,
            barPercentage: 0.8,
            categoryPercentage: 0.9
          }]
        },
        options: {
          indexAxis: \'y\',
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { display: false } },
          scales: {
            x: { display: false, grid: { display: false } },
            y: { grid: { display: false, drawBorder: false }, ticks: { font: { size: 12, weight: \'bold\', family: \'Roboto\' }, color: \'#002060\' } }
          }
        },
        plugins: [{
          id: \'customLabels\',
          afterDatasetsDraw: function(chart) {
            var ctx = chart.ctx;
            chart.data.datasets.forEach(function(dataset, i) {
              var meta = chart.getDatasetMeta(i);
              meta.data.forEach(function(bar, index) {
                var value = dataset.data[index];
                if(value > 0){
                   ctx.fillStyle = \'#ffffff\';
                   ctx.font = \'bold 12px Roboto\';
                   ctx.textAlign = \'right\';
                   ctx.textBaseline = \'middle\';
                   ctx.fillText(value, bar.x - 6, bar.y);
                }
              });
            });
          }
        }]
      });
    }

    // --- UPDATED LEADERBOARD CHART (Compact & Scrollable) ---
    function updateStudentRankingChart() {
      if (typeof Chart === \'undefined\') return;

      var students = computeStudentMarksFromTable();
      if (!students || students.length === 0) return;

      students.sort(function(a, b) { return b.value - a.value; });

      var labels = students.map(function(s) { return s.label; });
      var values = students.map(function(s) { return s.value; });

      var $canvas = $(\'#perf-student-ranking-canvas\');
      if (!$canvas.length) return;

      // 1. CALCULATE COMPACT HEIGHT (35px per student)
      var rowHeight = 35; 
      var calculatedHeight = (students.length * rowHeight) + 50;
      var finalHeight = Math.max(calculatedHeight, 400);

      // 2. FORCE CANVAS HEIGHT
      $canvas.css(\'height\', finalHeight + \'px\');
      $canvas.attr(\'height\', finalHeight);

      var bgColors = values.map(function(val, index) {
        if (index === 0) return \'#dc2626\'; // Red
        if (index === values.length - 1) return \'#f59e0b\'; // Orange
        return \'#002060\'; // Blue
      });

      if (window.perfRankingChart) window.perfRankingChart.destroy();
      var ctx = $canvas[0].getContext(\'2d\');

      window.perfRankingChart = new Chart(ctx, {
        type: \'bar\',
        data: {
          labels: labels,
          datasets: [{ 
              label: \'Mark\', 
              data: values, 
              backgroundColor: bgColors, 
              barPercentage: 0.7, 
              categoryPercentage: 0.9 
          }]
        },
        options: {
          indexAxis: \'y\',
          responsive: true,
          maintainAspectRatio: false, // Critical for scrolling
          plugins: { legend: { display: false } },
          layout: { padding: { right: 20 } },
          scales: {
            x: { display: false, max: 100 },
            y: { 
                grid: { display: false }, 
                ticks: { 
                    font: { family: \'Roboto\', size: 11, weight: \'bold\' }, 
                    color: \'#374151\', 
                    autoSkip: false 
                } 
            }
          }
        },
        plugins: [{
          id: \'leaderboardLabels\',
          afterDatasetsDraw: function(chart) {
            var ctx = chart.ctx;
            chart.data.datasets.forEach(function(dataset, i) {
              var meta = chart.getDatasetMeta(i);
              meta.data.forEach(function(bar, index) {
                var value = dataset.data[index];
                if(value > 0){
                   ctx.fillStyle = \'#ffffff\';
                   ctx.font = \'bold 11px Roboto\';
                   ctx.textAlign = \'right\';
                   ctx.textBaseline = \'middle\';
                   ctx.fillText(value, bar.x - 6, bar.y);
                }
              });
            });
          }
        }]
      });
    }

    function updatePerformanceTabFromStats() {
      if (window.marksPerfStats) {
        var s = window.marksPerfStats;
        if ($(\'#perf-total-students\').length) $(\'#perf-total-students\').text(s.totalStudents || \'--\');
        if ($(\'#perf-posted-count\').length) $(\'#perf-posted-count\').text(s.postedCount || 0);
        if ($(\'#perf-pass-rate\').length) $(\'#perf-pass-rate\').text(s.passRate != null ? s.passRate + \'%\' : \'--%\');
        if ($(\'#perf-fail-rate\').length) $(\'#perf-fail-rate\').text(s.failRate != null ? s.failRate + \'%\' : \'--%\');
        if ($(\'#perf-highest-mark\').length) $(\'#perf-highest-mark\').text(s.highestMark != null ? s.highestMark : \'--\');
        if ($(\'#perf-highest-student\').length) $(\'#perf-highest-student\').text(s.highestStudent ? \'Student: \' + s.highestStudent : \'Student: --\');
        if ($(\'#perf-lowest-mark\').length) $(\'#perf-lowest-mark\').text(s.lowestMark != null ? s.lowestMark : \'--\');
        if ($(\'#perf-lowest-student\').length) $(\'#perf-lowest-student\').text(s.lowestStudent ? \'Student: \' + s.lowestStudent : \'Student: --\');
        if ($(\'#perf-class-average\').length) $(\'#perf-class-average\').text(s.classAverage != null ? s.classAverage : \'--\');
        if ($(\'#perf-captured-count\').length) $(\'#perf-captured-count\').text(s.capturedCount != null ? s.capturedCount : \'--\');
        if ($(\'#perf-no-marks-count\').length) $(\'#perf-no-marks-count\').text(s.noMarksCount != null ? s.noMarksCount : \'--\');
      }

      updateGradeBarChartFromTable();
      updateStudentRankingChart(); 
    }

    // =========================================================
    // 3. COMPARATIVE / COHORT CHARTS (AJAX)
    // =========================================================

    function fetchCohortData() {
        $(\'#cohort-loading\').css(\'display\', \'flex\');

        var students = computeStudentMarksFromTable();
        var studentIds = [];
        if (students && students.length > 0) {
            studentIds = students.map(function(s) { return s.reg; });
        } else {
            $(\'#cohort-loading\').hide();
            return;
        }


		

        var xhr = new XMLHttpRequest();
        xhr.open("POST", "' . $this->core->conf['conf']['path'] . '/api/performance", true);
        xhr.setRequestHeader("Content-Type", "application/json");

        xhr.onreadystatechange = function() {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200) {
                    try {
                        var response = JSON.parse(xhr.responseText);
                        console.log("SERVER RESPONSE:", response);
                        
                        if (response.status === "success") {
                            handleCohortData(response);
                        } else {
                            console.error("Server Error:", response.message);
                            $(\'#cohort-loading\').hide();
                        }
                    } catch (e) {
                        console.error("JSON Parse error", e);
                        $(\'#cohort-loading\').hide();
                    }
                } else {
                    console.error("HTTP Error:", xhr.status);
                    $(\'#cohort-loading\').hide();
                }
            }
        };

        var jsonData = JSON.stringify({
            classCode: "' . $shortName . '",
            part: "' . $selectedyearofstudy . '",
            semester: "' . $selectedsemester . '",
            examYear: "' . $academicyear . '",
            currentCourseCode: "' . $__course_code . '", 
            student_numbers: studentIds
        });
        
        xhr.send(jsonData);
    }

    function handleCohortData(data) {
        $(\'#cohort-loading\').hide();
        hasFetchedCohortData = true;

        var localStudents = computeStudentMarksFromTable();
        if (!localStudents || localStudents.length === 0) return;

        var sum = localStudents.reduce(function(a, b){ return a + b.value; }, 0);
        var currentAvg = Math.round(sum / localStudents.length);

        renderGlobalComparisonChart(currentAvg, parseFloat(data.combined_other_avg) || 0);

        var labels = [];
        var thisCourseMarks = [];
        var otherCourseAvgs = [];

        localStudents.sort(function(a,b) { return b.value - a.value; });

        var safeAverages = data.student_averages || {};

        localStudents.forEach(function(s) {
            labels.push(s.label);
            thisCourseMarks.push(s.value);
            var other = (safeAverages[s.reg] !== undefined) ? safeAverages[s.reg] : 0;
            otherCourseAvgs.push(other);
        });

        renderStudentContextChart(labels, thisCourseMarks, otherCourseAvgs);
    }

    function renderGlobalComparisonChart(thisAvg, othersAvg) {
        var $canvas = $(\'#perf-global-comparison-canvas\');
        if (!$canvas.length) return;
        var ctx = $canvas[0].getContext(\'2d\');

        if (window.perfGlobalCompChart) window.perfGlobalCompChart.destroy();

        window.perfGlobalCompChart = new Chart(ctx, {
            type: \'bar\',
            data: {
                labels: [\'This Course\', \'Other Courses (Avg)\'],
                datasets: [{
                    data: [thisAvg, othersAvg],
                    backgroundColor: [\'#0ea5e9\', \'#6b7280\'],
                    borderRadius: 5,
                    barThickness: 60
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, max: 100, grid: { borderDash: [2, 2] } },
                    x: { grid: { display: false } }
                }
            },
            plugins: [{
                id: \'avgLabels\',
                afterDatasetsDraw: function(chart) {
                    var ctx = chart.ctx;
                    chart.data.datasets.forEach(function(dataset, i) {
                        var meta = chart.getDatasetMeta(i);
                        meta.data.forEach(function(bar, index) {
                            var data = dataset.data[index];
                            ctx.fillStyle = \'#111827\';
                            ctx.font = \'bold 13px Roboto\';
                            ctx.textAlign = \'center\';
                            ctx.fillText(data + \'%\', bar.x, bar.y - 8);
                        });
                    });
                }
            }]
        });
    }

    // --- STUDENT CONTEXT CHART (REDUCED SIZE - 55px) ---
    function renderStudentContextChart(labels, thisMarks, otherAvgs) {
      var canvasElement = document.getElementById("perf-student-context-canvas");
      if (!canvasElement) return;

      var rowHeight = 55; // Reduced from 90 to 55
      var calculatedHeight = (labels.length * rowHeight) + 100;
      var finalHeight = Math.max(calculatedHeight, 450);

      canvasElement.style.height = finalHeight + "px";
      canvasElement.height = finalHeight;

      if (window.perfStudentContextChart) window.perfStudentContextChart.destroy();

      var ctx = canvasElement.getContext("2d");

      window.perfStudentContextChart = new Chart(ctx, {
        type: "bar",
        data: {
          labels: labels,
          datasets: [
            {
              label: "This Course",
              data: thisMarks,
              backgroundColor: "#0ea5e9",
              borderRadius: 4,
              barThickness: 12, // Thinner bars
              maxBarThickness: 16
            },
            {
              label: "Avg in Others",
              data: otherAvgs,
              backgroundColor: "#f59e0b",
              borderRadius: 4,
              barThickness: 12, // Thinner bars
              maxBarThickness: 16
            }
          ]
        },
        options: {
          indexAxis: "y",
          responsive: false, // Strict sizing
          maintainAspectRatio: false,
          plugins: {
            legend: { position: "top", align: "start" },
            tooltip: { mode: "index", intersect: false }
          },
          layout: { padding: { left: 5, right: 20, top: 10, bottom: 10 } },
          scales: {
            x: {
              beginAtZero: true,
              max: 100,
              position: "top",
              grid: { color: "#f3f4f6" }
            },
            y: {
              grid: { display: false },
              ticks: {
                autoSkip: false,
                font: { size: 11, family: "Roboto", weight: "500" },
                color: "#111827"
              }
            }
          }
        }
      });
    }

    $(function () {
        $(\'#marks-tab-performance\').on(\'click\', function (e) {
            setTimeout(function () { updatePerformanceTabFromStats(); }, 50);
            if (!hasFetchedCohortData) {
                setTimeout(fetchCohortData, 100);
            }
        });
    });

  })(jQuery);
</script>';




						echo'	<div id="tab2" class="tab-pane fade">
							
        <!-- Add more content specific to carrying students here -->
        
						<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
											
						
						<br> 
        <div  style="padding: 8px 10px; background-color: #ffffff; border-bottom: 1px solid #dee2e6;">
						<div style="font-family: \'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif; font-size: 13px; color: #333; font-weight: 600;">Carry Students  list</div>
						<div style="font-family: \'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif; font-size: 9px; color: #666;">Listed are registed, Click add to add more</div>
      <div>	
								




							
							<div style="float:right; display:flex; align-items:center; gap:0;">




 <!-- Post Coursework Only -->
          <button
            id="postcourseworkonly"
            data-toggle="modal"
            data-target="#requestcourse"
            title="Post Coursework Only"
            onmouseover="this.style.background=\'#f3f4f6\'"
            onmouseout="this.style.background=\'#ffffff\'"
            onfocus="this.style.outline=\'none\'"
            onblur="this.style.outline=\'none\'"
            style="
              display:inline-flex;align-items:center;gap:10px;
              padding:8px 12px;background:#ffffff;color:#000000;
              border:none;border-radius:6px;box-shadow:none;cursor:pointer;
              font-size:12px;font-weight:700;
            "
          >


<i class="ri-loop-right-line" aria-hidden="true" style="font-size:16px;color:#000000;line-height:1;"></i>

<span style="color:#000000;">Post Carry Course Ass. Only</span>
          </button>


           <!-- Pipe -->
          <span aria-hidden="true" style="padding:0 10px;color:#9ca3af;">|</span>



          <!-- Post Carry Marks -->
          <button
            id="postcarrybtn"
            data-toggle="modal"
            data-target="#requestcourse"
            title="Post Carry Marks"
            onmouseover="this.style.background=\'#f3f4f6\'"
            onmouseout="this.style.background=\'#ffffff\'"
            onfocus="this.style.outline=\'none\'"
            onblur="this.style.outline=\'none\'"
            style="
              display:inline-flex;align-items:center;gap:10px;
              padding:8px 12px;background:#ffffff;color:#000000;
              border:none;border-radius:6px;box-shadow:none;cursor:pointer;
              font-size:12px;font-weight:700;
            "
          >
           <i class="ri-check-double-line" aria-hidden="true" style="font-size:16px;color:#000000;line-height:1;"></i>


            <span style="color:#000000;">Post Carry Exam & Course Asses.</span>
          </button>



         
         

          <!-- Pipe -->
          <span aria-hidden="true" style="padding:0 10px;color:#9ca3af;">|</span>

          <!-- Export to PDF (Carry) -->
          <button
            id="export-carry-pdf-button"
            class="export-carry-pdf-button"
            data-toggle="modal"
            data-target="#requestcourse"
            title="Export Carry List to PDF"
            onmouseover="this.style.background=\'#f3f4f6\'"
            onmouseout="this.style.background=\'#ffffff\'"
            onfocus="this.style.outline=\'none\'"
            onblur="this.style.outline=\'none\'"
            style="
              display:inline-flex;align-items:center;gap:10px;
              padding:8px 12px;background:#ffffff;color:#000000;
              border:none;border-radius:6px;box-shadow:none;cursor:pointer;
              font-size:12px;font-weight:700;
            "
          >
            <i class="ri-file-pdf-2-line" aria-hidden="true" style="font-size:16px;color:#000000;line-height:1;"></i>
            <span style="color:#000000;">Export to PDF</span>
          </button>

          <!-- Pipe -->
          <span aria-hidden="true" style="padding:0 10px;color:#9ca3af;">|</span>

          <!-- Add Missing Students -->
          <button
            id="missing-students"
            class="btn"
            data-toggle="modal"
            data-target="#requestcourse"
            title="Add Missing Students"
            onmouseover="this.style.background=\'#f3f4f6\'"
            onmouseout="this.style.background=\'#ffffff\'"
            onfocus="this.style.outline=\'none\'"
            onblur="this.style.outline=\'none\'"
            style="
              display:inline-flex;align-items:center;gap:10px;
              padding:8px 12px;background:#ffffff;color:#000000;
              border:none;border-radius:6px;box-shadow:none;cursor:pointer;
              font-size:12px;font-weight:700;
            "
          >
            <i class="ri-user-add-line" aria-hidden="true" style="font-size:16px;color:#000000;line-height:1;"></i>
            <span style="color:#000000;">Add Missing Students</span>
          </button>
        </div>

                        
                        </div>
                        
                        <br>
                        <br>
                            <div class="table-search"
                                  style="position:relative; max-width:320px; margin:0; display:inline-block;
                                          font-family: Roboto, system-ui, -apple-system, \'Segoe UI\', Arial, sans-serif;
                                          border-bottom:3px solid #000; padding-bottom:6px;">
                                <!-- Left search icon -->
                                <i class="ri-search-line"
                                  style="position:absolute; left:10px; top:50%; transform:translateY(-50%);
                                          color:#000; font-size:18px; pointer-events:none;"></i>

                                <!-- Input (no border) -->
                                <input
                                  type="text"
                                  id="' . $searchId . '"
                                  placeholder="Search this table…"
                                  data-target="#' . $tableId . '"
                                  aria-label="Search table"
                                  onkeyup="filterTable(this)"
                                  onfocus="this.style.boxShadow=\'inset 0 0 0 2px rgba(0,0,0,.08)\';"
                                  onblur="this.style.boxShadow=\'none\';"
                                  style="width:100%; padding:8px 30px 8px 32px;
                                        border:0; border-radius:6px;
                                        font-size:12px; color:#000; outline:none;
                                        background:#fff;
                                        font-family: Roboto, system-ui, -apple-system, \'Segoe UI\', Arial, sans-serif;
                                        caret-color:#000;"
                                />

                                <!-- Right clear button -->
                                <button
                                  type="button"
                                  title="Clear"
                                  onclick="var el=document.getElementById(\'' . $searchId . '\'); if(el){ el.value=\'\'; filterTable(el); }"
                                  style="position:absolute; right:6px; top:50%; transform:translateY(-50%);
                                        background:transparent; border:0; padding:0; margin:0; cursor:pointer;
                                        display:inline-flex; align-items:center; justify-content:center;
                                        height:22px; width:22px; border-radius:50%;"
                                >
                                  <i class="ri-close-circle-line" aria-hidden="true"
                                    style="font-size:18px; color:#000; line-height:1;"></i>
                                </button>
                              </div>

                              <style>
                                #' . $searchId . '::placeholder { color:#111827; opacity:0.9; }
                              </style>
                        <br>
                        <br>

                        <div class="table-responsive">
                      <table id="carry_students_table" class="table table-hover table-bordered marks_table" style="float:right; padding:0px; color:black; font-family: Roboto, Tahoma, Geneva, Verdana, sans-serif; font-size: 10px; width: 100%;">
                      <thead style="font-weight:bold;">
                        <tr style="background-color: #F8F8F8; color: #000000;">
                          <th>#</th>
                          <th>Reg Number</th>
                          <th>Name</th>
                          <th>Surname</th>
                          
             <!-- Add Carry Students (Centered + HP Smart-like font stack + quotes escaped for PHP single-quoted string) -->
<div
  class="modal fade"
  id="add_carry_student"
  tabindex="-1"
  role="dialog"
  aria-hidden="true"
  style="font-family:\'HP Simplified\',\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif; font-size:13px;"
>
  <div class="modal-dialog modal-md modal-dialog-centered" role="document" style="max-width:560px;margin:0 auto;">
    <div
      class="modal-content"
      style="
        background:#ffffff;
        border-radius:10px;
        border:1px solid #d1d5db;
        box-shadow:0 22px 55px rgba(0,0,0,0.28);
        overflow:hidden;
      "
    >
      <!-- Header -->
      <div
        class="modal-header"
        style="
          display:flex;align-items:center;justify-content:space-between;
          padding:12px 16px;
          border-bottom:1px solid #e5e7eb;
          background:#ffffff;
        "
      >
        <div style="display:flex;align-items:center;gap:10px;">
          <span
            style="
              display:inline-flex;align-items:center;justify-content:center;
              width:26px;height:26px;
              border:1px solid #111111;border-radius:6px;
              background:#ffffff;
            "
            title="Add"
          >
            <i class="fa fa-user-plus" aria-hidden="true" style="font-size:13px;color:#111111;"></i>
          </span>

          <h5
            class="modal-title"
            id="addStudentModalLabel"
            style="margin:0;font-size:14px;font-weight:800;color:#111111;"
          >
            Add Carry Students
          </h5>
        </div>

        <button
          type="button"
          class="close"
          data-dismiss="modal"
          aria-label="Close"
          style="
            background:transparent;border:none;cursor:pointer;
            padding:6px 8px;border-radius:8px;
            font-size:18px;line-height:1;color:#111111;
          "
          onmouseover="this.style.background=\'#f3f4f6\'"
          onmouseout="this.style.background=\'transparent\'"
        >
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <!-- Body -->
      <div class="modal-body" style="padding:14px 16px;">
        <div style="color:#111111;line-height:1.5;margin-bottom:10px;">
          You are about to add <b>carry</b> students for this class. Enter one or more student numbers below.
        </div>

        <label for="studentNumberInput" style="display:block;font-weight:800;color:#111111;margin-bottom:6px;">
          Add Student Number(s)
        </label>

        <input
          type="text"
          id="studentNumberInput"
          maxlength="10"
          class="form-control"
          placeholder="Enter student numbers here"
          style="
            width:100%;
            padding:10px 12px;
            border:1px solid #d1d5db;
            border-radius:8px;
            background:#ffffff;
            color:#111111;
            font-size:13px;
            margin-bottom:12px;
            box-shadow:none;
            outline:none;
          "
          onfocus="this.style.borderColor=\'#111111\';this.style.boxShadow=\'0 0 0 3px rgba(17,17,17,0.12)\';"
          onblur="this.style.borderColor=\'#d1d5db\';this.style.boxShadow=\'none\';"
        />

        <div
          id="studentNumbersContainer"
          style="
            padding:10px;
            background:#f3f4f6;
            border-radius:8px;
            min-height:48px;
            color:#111111;
          "
        >
          <!-- chips / numbers will render here -->
        </div>
      </div>

      <!-- Footer -->
      <div
        class="modal-footer"
        style="
          display:flex;align-items:center;justify-content:flex-end;gap:10px;
          padding:12px 16px;
          border-top:1px solid #e5e7eb;
          background:#ffffff;
        "
      >
        <button
          type="button"
          data-dismiss="modal"
          style="
            display:inline-flex;align-items:center;gap:8px;
            padding:8px 12px;
            background:transparent;
            border:none;
            border-radius:8px;
            cursor:pointer;
            font-size:12px;font-weight:800;
            color:#111111;
          "
          onmouseover="this.style.background=\'#f3f4f6\'"
          onmouseout="this.style.background=\'transparent\'"
        >
          <i class="fa fa-times" aria-hidden="true" style="font-size:13px;color:#111111;"></i>
          <span style="letter-spacing:0.3px;">CANCEL</span>
        </button>

        <button
          id="submitStudentNumbers"
          type="button"
          data-dismiss="modal"
          style="
            display:inline-flex;align-items:center;gap:8px;
            padding:8px 14px;
            background:#111111;
            color:#ffffff;
            border:1px solid #111111;
            border-radius:8px;
            cursor:pointer;
            font-size:12px;font-weight:900;
          "
          onmouseover="this.style.background=\'#000000\'"
          onmouseout="this.style.background=\'#111111\'"
        >
          <i class="fa fa-check" aria-hidden="true" style="font-size:13px;color:#ffffff;"></i>
          <span style="letter-spacing:0.3px;">SUBMIT</span>
        </button>
      </div>
    </div>
  </div>
</div>';

//now we deal with carry students

		if ($assessmentWeight > 0) {
			$assessmentIds = [];
			if (!empty($assessmentData)) {
				// Iterate through each row
				foreach ($assessmentData as $row) {
					// Access the value of the coursework_title column
					$assessmentTitle = $row['coursework_title'];
					$assessmentid =  $row['courseID'];
					$courseworkId = $courseworkIds[$i];

					// Add the assessment id to the array
					$assessmentIds[] = $assessmentid;

					// Add the coursework_title value as a new table column
					// If there is no courseweight, we don't need courseweight
					echo '<th datatype="numeric" class="assessment-column" id="' . $assessmentid . '" data-total-mark="' . $row['total_mark'] . '" style="font-family: Arial, sans-serif; font-size: 9px;">' . $assessmentTitle . '<br><b>(' . $row['total_mark'] . ')</b></th>';
				}
			}

			echo '<th id="cw">OCW</th>';
		}

		if ($otherExam != -1 && $otherExam != NULL) {
			echo '<th datatype = "numeric" id="otherExam">Other Exam</th>';
		}
		if ($examWeight != 0) {

			echo '<th datatype = "numeric" id = "exam" >Exam</th>';
		}


		echo '<th style="text-align: center; " datatype = "numeric"  id = "mark" >OM%</th>
															<th datatype = "numeric"  id = "grade" >Grade</th>
															<th datatype = "numeric"  id = "remark" >Remark</th>
														  
															<th datatype = "string"  id = "comment" >Comment</th>
															
														   
														  
														</tr>

    </thead>
    <tbody>';

//load carry students


		// s.ID = '$selectedprogID'-- THIS CAN BE REMOVED IF AT NUST THEY DO ALLOW CROSS PROGRAMMES

		//$carry_student_sql ="SELECT bi.FirstName AS StudentName, bi.Surname AS StudentSurname, sp.part AS yearofstudy, cl.semester AS semester, bi.ID AS StudentNumber, s.Name AS progName, sp.programme_code AS progcode, cl.year AS academicyear FROM `student_progression` sp INNER JOIN `study` s ON s.ShortName = sp.programme_code AND s.ProgrammesAvailable = 1 AND s.ID = '$selectedprogID' INNER JOIN `basic-information` bi ON bi.ID = sp.student_id INNER JOIN `courselecture` cl ON cl.classcode = s.ID  LEFT JOIN `course-electives` ce ON ce.CourseID = cl.coursecode AND ce.StudentID = bi.ID and ce.StudentPart = '$yearofstudy' AND ce.Semester = '$semester' AND ce.PeriodID = '$periodID' AND ce.Seating like '%arry%'  LEFT JOIN `carriedcourse_student_link` csl ON csl.course_code = cl.coursecode AND csl.period = '$periodID'  AND csl.student_id = bi.ID AND csl.course_code = '$course_id' WHERE (ce.StudentID IS NOT NULL OR csl.student_id IS NOT NULL) GROUP BY bi.ID ORDER BY bi.Surname; ";


        //REGULATIONCODE IN NOW

							$carry_student_sql = "	SELECT 
						bi.FirstName AS StudentName, 
						bi.Surname AS StudentSurname, 
						sp.part AS yearofstudy, 
						cl.semester AS semester, 
						bi.ID AS StudentNumber, 
						s.Name AS progName, 
						sp.programme_code AS progcode, 
						cl.year AS academicyear 
					FROM 
						`student_progression` sp 
					INNER JOIN 
						`study` s ON s.ShortName = sp.programme_code 
						AND s.ProgrammesAvailable = 1 
						AND s.ID = '$selectedprogID' 
					INNER JOIN 
						`basic-information` bi ON bi.ID = sp.student_id 
					INNER JOIN 
						`courselecture` cl ON cl.classcode = s.ID  
					LEFT JOIN 
						`course-electives` ce ON ce.CourseID = cl.coursecode 
						AND ce.StudentID = bi.ID 
						AND ce.StudentPart = '$yearofstudy' 
						AND ce.Semester = '$semester' 
						AND ce.PeriodID = '$periodID' 
						AND ce.Seating LIKE '%arry%'  
					LEFT JOIN 
						`carriedcourse_student_link` csl ON csl.course_code = cl.coursecode 
						AND csl.period = '$periodID'  
						AND csl.student_id = bi.ID 
						AND csl.course_code = '$course_id' 
					LEFT JOIN 
						`student-study-link` `ssl` ON `ssl`.StudentID = sp.student_id
					WHERE 
					 sp.exam_centre = '$selectedcampus' AND	(ce.StudentID IS NOT NULL OR csl.student_id IS NOT NULL)";

					// Modify the query to include filtering based on regulation codes if the filter is set
					if ($regulationCode) {
						if ($lastYearInRegulationCode >= 2023) {
							// Include students with regulation codes of that year or greater, or those without a regulation code
							$carry_student_sql .= " AND (`ssl`.regulation_code IS NULL OR SUBSTRING(`ssl`.regulation_code, -4) >= '$lastYearInRegulationCode')";
						} else {
							// Include students with regulation codes strictly matching that year, using SUBSTRING to get the last 4 characters
							$carry_student_sql .= " AND SUBSTRING(`ssl`.regulation_code, -4) = '$lastYearInRegulationCode'";
						}
					}

					$carry_student_sql .= "
					GROUP BY
						bi.ID
					ORDER BY
						bi.Surname;";



		//echo $carry_student_sql;





		$carry_student_sql_run = $this->core->database->doSelectQuery($carry_student_sql);


		$c =0;


		$carryResults = []; // This will store our multi-dimensional array of results


		while ($runx = $carry_student_sql_run->fetch_assoc()) {

			$c++;

			$student_number = $runx['StudentNumber']; // Initialize $student_number correctly



			// Initialize the student data if not already done
			if (!isset($carryResults[$student_number])) {
				$carryResults[$student_number] = [
					'c' => $c,
					'RegNumber' => $runx['StudentNumber'],
					'Name' => strtoupper($runx['StudentName']),
					'Surname' => strtoupper($runx['StudentSurname']),
					'part' => $runx['part'],
					'programme' => $runx['prog'],
					'semester' => $runx['semester'],
					'year' => $runx['year'],
					'className' => $class,
					'classCode' => $progcode,
					'related_records' => []
				];
			}


			$courseworkresultsSql = "SELECT cr.courseworkId, cr.totalMark
					FROM edurole.courseworkresults cr
					INNER JOIN coursework cw ON cr.courseworkID = cw.ID
					LEFT JOIN courseresultssummary crs ON crs.courselecture_id = cw.lecturer_course_id
					WHERE cr.student_id = '{$runx['StudentNumber']}' AND cw.lecturer_course_id = '$selectedclass' group by courseworkId";




			//echo ($courseworkresultsSql);
//
//			echo 'course '.$resultsSql. 'course';
//
			//	exit();


			$courseworkresultsSql_run = $this->core->database->doSelectQuery($courseworkresultsSql);



			while ($secondaryRow = $courseworkresultsSql_run->fetch_assoc()) {
				$carryResults[$student_number]['related_records'][] = [
					'courseworkId' => $secondaryRow['courseworkId'],
					'totalMark' => $secondaryRow['totalMark']
				];
			}




			$resultsSql = "SELECT *
							FROM
							edurole.courseresultssummary
						WHERE 
						courseresultssummary.`student_id` = '{$runx['StudentNumber']}'
						AND
					   courselecture_id = '$selectedclass'" ;

			// Execute the SQL query
			$resultsSql_run = $this->core->database->doSelectQuery($resultsSql);

			// Fetch and add to the array, this arry will serve to keep if there are any, the results records for the students
			while ($resultsRow = $resultsSql_run->fetch_assoc()) {
				$carryResults[$student_number]['exam_records'][] = [
					'ID' => $resultsRow['ID'],
					'courseWorkMark' => $resultsRow['courseWorkMark'],
					'finalExaminationMark' => $resultsRow['finalExaminationMark'],
					//ADDED THE COLUMN THAT WILL HANDLE THE otherExam
					'overallMark' => $resultsRow['overallMark'],
					'otherExam' => $resultsRow['otherExam'],
					'resultGrade' => $resultsRow['resultGrade'],
					'courseRemark' => $resultsRow['courseRemark'],
					'comment' => $resultsRow['comment'],

					//added these for publishing board_reviewed = 1 published at department
					'board_reviewed' => $resultsRow['board_reviewed']

				];
			}






		}

		//print_r( $carryResults);

		foreach ($carryResults as $student)

		{
			echo '<tr id = "'. $student['RegNumber'] .' ">';
			echo '<td style = "background-color: #F0F0F0;">' . $student['c'] . '</td>';
			echo '<td style = "background-color: #F0F0F0;"><b>' . $student['RegNumber'] . '</b></td>';
			echo '<td style = "background-color: #F0F0F0;">' . $student['Name'] . '</td>';
			echo '<td style = "background-color: #F0F0F0;">' . $student['Surname'] . '</td>';



			$examRecords = $student['exam_records'][0];
			// Determine the class name based on 'board_reviewed' value
//			$editableClass = (isset($examRecords['board_reviewed']) && $examRecords['board_reviewed'] > 0) ? '' : 'editable';
			// Check the value of 'board_reviewed'




			$editableClass = (isset($examRecords['board_reviewed']) && $examRecords['board_reviewed'] > -1) ? '' : 'editable';
			//$contentEditableAttribute = (isset($examRecords['board_reviewed']) && $examRecords['board_reviewed'] == -1) ? '': 'contenteditable="true"' ;






			// Check if overallMark is set and less than 50, then WE set font color to red
			$overallMarkStyle = '';
			$overallMarkStyle2 = '';
			if (isset($examRecords['overallMark']) && $examRecords['overallMark'] < 50) {
				$overallMarkStyle = ' style="color: red;"';
				$overallMarkStyle2 = ' color: red;';
			}



			//now this ensures that the specified block of code only runs when $assessmentWeight is greater than 0.
			if ($assessmentWeight > 0) {
				// Now we loop through each piece of coursework to fill in the additional cells
				foreach ($assessmentData as $assessment) {
					$courseworkId = $assessment['courseID']; // The ID from coursework query

					// Search for matching courseworkId in the student's related records, USING ARRAY
					$matchingRecord = array_filter($student['related_records'], function ($record) use ($courseworkId) {
						return $record['courseworkId'] == $courseworkId;
					});

					// Check if we found a matching record
					if (!empty($matchingRecord)) {
						// Since array_filter preserves keys, we reset to get the first element
						$matchingRecord = reset($matchingRecord);

						// Extract the total mark
						$value = $matchingRecord['totalMark'];

						echo '<td class="' . $editableClass . '" ' . $overallMarkStyle . ' data-coursework-id="' . $courseworkId . '">' . $value . '</td>';
					} else {
						// If there is no matching coursework TM
						echo '<td class="' . $editableClass . '" ' . $overallMarkStyle . ' data-coursework-id="' . $courseworkId . '"></td>';
					}
				}

				echo '<td ' . $overallMarkStyle . ' style="background-color: #F0F0F0;">' . htmlspecialchars($examRecords['courseWorkMark'] ?? '') . '</td>';
			}

//modify to echo the otherExam mark

//				echo "Exam Weight ".$otherExam;


			// Check and echo the 'other Exam' mark if available
			// Check if $otherExam is set and not -1 or NULL, then echo the 'other Exam' mark
			if ($examWeight != -1 && ($otherExam != -1)) {
				echo '<td  class="' . $editableClass . '"' . $overallMarkStyle . '>'. htmlspecialchars($examRecords['otherExam'] ?? '') . '</td>';
			}
//					echo "Exam Weight ".$examWeight. "check test ".$examRecords['finalExaminationMark'];

//// Check if $exam is set or not
			if ($examWeight != 0 )
			{
				echo '<td  class="' . $editableClass . '"' . $overallMarkStyle . '>'. htmlspecialchars($examRecords['finalExaminationMark'] ?? '') . '</td>';
			}
//					// Check if $exam is set or not
//
//                      echo '<td class="' . $editableClass . '"' . $overallMarkStyle . '>'. htmlspecialchars($examRecords['finalExaminationMark'] ?? '') . '</td>';

//				echo '<td class="' . $editableClass . '"' . $overallMarkStyle . '>50</td>';
//
			$overallMark = isset($examRecords['overallMark']) ? (int)round($examRecords['overallMark']) : '';
			echo '<td  style="text-align: center; font-weight: bold;background-color: #F0F0F0;' . $overallMarkStyle2 . '">' . htmlspecialchars($overallMark) . '</td>';

			echo '<td ' . $overallMarkStyle . 'style = "background-color: #F0F0F0;">' . htmlspecialchars($examRecords['resultGrade'] ?? '') . '</td>';
			echo '<td ' . $overallMarkStyle . 'style = "background-color: #F0F0F0;">'. htmlspecialchars($examRecords['courseRemark'] ?? '') . '</td>';
			echo '<td  class="' . $editableClass . '"' . $overallMarkStyle . '>' . htmlspecialchars($examRecords['comment'] ?? '') . '</td>';



		}


		echo '</tbody>
</table></div>
						   
                  
                </div>
            </div>
        </div>
    </div>
</div>

';

		//modal for carry students

		echo'
		
			<!-- Success Modal -->
<div class="modal fade" id="cary-success-modal" tabindex="-1" role="dialog" aria-labelledby="successModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="successModalLabel">Success</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Students added. Select the "Carry Students" tab and start capturing marks.
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" data-dismiss="modal">Start Capturing</button>
            </div>
        </div>
    </div>
</div>

<!-- Error Modal -->
<div class="modal fade" id="carry-errorModal" tabindex="-1" role="dialog" aria-labelledby="errorModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="errorModalLabel">Error</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                An error occurred. Please try again.
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
			
			
			
	
		
		';






		$columns = [
			"#", "Reg Number", "Name", "Surname"
		];

		if (!empty($assessmentData)) {
			foreach ($assessmentData as $row) {
				$columns[] = $row['coursework_title'] . ' (' . $row['total_mark'] . ')';
			}
		}

		$columns = array_merge($columns, ["CW", "Exam", "Mark Att %", "Grade", "Remark", "Conf", "Comment"]);
		$columnsJson = json_encode($columns);

		echo '

    <style>
  .nust-modal-content{
    font-family:Roboto,system-ui,-apple-system,"Segoe UI",Arial,sans-serif;
    color:#000;
    border-radius:10px;
    border:0;
    box-shadow:0 18px 40px rgba(0,0,0,.18);
  }

  .nust-modal-header{
    background:#f9fafb;
    padding:10px 16px;
    border-bottom:0;
  }

  .nust-modal-title{
    display:flex;
    align-items:center;
    gap:8px;
    font-size:13.5px;
    font-weight:700;
    margin:0;
    color:#000;
  }

  .nust-modal-title i{
    font-size:18px;
    color:#000;
  }

  .nust-modal-body{
    font-size:13.5px;
    padding:12px 16px;
  }

  .nust-modal-footer{
    border-top:0;
    padding:8px 16px 12px;
    display:flex;
    justify-content:flex-end;
    gap:10px;
  }

  .nust-btn{
    border:0;
    border-radius:6px;
    padding:5px 14px;
    font-size:13px;
    font-weight:700;
    display:inline-flex;
    align-items:center;
    gap:6px;
    cursor:pointer;
    background:transparent;
    color:#000;
  }

  .nust-btn-primary{
    background:#000;
    color:#fff;
  }

  .nust-btn:hover{
    background:#f3f4f6;
  }

  .nust-btn-primary:hover{
    background:#111827;
  }

  .nust-btn i{
    font-size:16px;
  }

  .modal-header .close{
    opacity:0.9;
    text-shadow:none;
    border:0;
    background:transparent;
  }
  .modal-header .close span{
    font-size:18px;
  }
</style>


<div
      class="modal fade"
      id="CourseAssessOnlySuccessModal"
      tabindex="-1"
      role="dialog"
      aria-labelledby="SuccessModalLabel"
      aria-hidden="true"
      style="font-family: \'Roboto\', Calibri, Segoe UI, Arial, sans-serif; font-size: 15px;"
    >
      <div class="modal-dialog modal-dialog-centered modal-md" role="document">
        <div class="modal-content" style="
            background:#ffffff;
            border-radius:6px;
            border:1px solid #111111;
            box-shadow:0 12px 30px rgba(0,0,0,0.18);
            overflow:hidden;
          ">

          <!-- Header -->
          <div class="modal-header" style="
              padding:14px 18px;
              border-bottom:1px solid #111111;
              background:#ffffff;
            ">
            <h5
              class="modal-title"
              id="SuccessModalLabel"
              style="display:flex;align-items:center;gap:10px;font-weight:700;font-size:16px;margin:0;"
            >
              <i class="ri-checkbox-circle-line" aria-hidden="true" style="font-size:20px;line-height:1;"></i>
              <span>Course Assessment Posted</span>
            </h5>
            <button
              type="button"
              class="close"
              data-dismiss="modal"
              aria-label="Close"
              style="outline:none;"
            >
              <span aria-hidden="true" style="font-size:22px;">&times;</span>
            </button>
          </div>

          <!-- Body -->
          <div class="modal-body" style="padding:18px 18px 12px 18px; line-height:1.55;">
            <p style="margin-bottom:10px;">
              Course assessment marks were successfully posted. Examination marks are <strong>not posted</strong>.
            </p>

            <p style="margin-bottom:0;color:#6b7280;font-size:11px; line-height:1.35; display:flex; gap:8px; align-items:flex-start;">
              <i class="ri-information-line" aria-hidden="true" style="font-size:14px; line-height:1; margin-top:1px;"></i>
              <span>
                You can post examination marks later once they are captured and verified.
              </span>
            </p>
          </div>

          <!-- Footer -->
          <div class="modal-footer" style="
              padding:12px 16px 14px 16px;
              border-top:1px solid #111111;
              background:#ffffff;
              display:flex;
              justify-content:flex-end;
              align-items:center;
              gap:8px;
            ">

            <!-- Done / Continue -->
            <button
              type="button"
              class="btn btn-sm"
              data-dismiss="modal"
              id = "postingcourseworkdone"
              title="Done"
              onmouseover="this.style.background=\'#f3f4f6\'"
              onmouseout="this.style.background=\'#ffffff\'"
              onfocus="this.style.outline=\'none\'"
              onblur="this.style.outline=\'none\'"
              style="
                display:inline-flex;align-items:center;gap:10px;
                padding:8px 16px;
                background:#ffffff;
                color:#000000;
                border:1px solid #111111;
                border-radius:6px;
                box-shadow:none;
                cursor:pointer;
                font-size:13px;
                font-weight:700;
              "
            >
              <i class="ri-check-line" aria-hidden="true" style="font-size:18px;line-height:1;"></i>
              <span style="color:#000000;">Done</span>
            </button>

          </div>
        </div>
      </div>
    </div>

<!-- end of countinous assessment---
<!-- Success Modal (Centered + HP Smart-like font stack + inline styles + quotes escaped for PHP single-quoted string) -->
<div
  class="modal fade"
  id="SuccessModalCarry"
  tabindex="-1"
  role="dialog"
  aria-labelledby="SuccessModalLabel"
  aria-hidden="true"
  style="font-family:\'HP Simplified\',\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif; font-size:13px;"
>
  <div class="modal-dialog modal-dialog-centered modal-md" role="document" style="max-width:560px;margin:0 auto;">
    <div
      class="modal-content"
      style="
        background:#ffffff;
        border-radius:10px;
        border:1px solid #d1d5db;
        box-shadow:0 22px 55px rgba(0,0,0,0.28);
        overflow:hidden;
      "
    >
      <!-- Header -->
      <div
        class="modal-header"
        style="
          display:flex;align-items:center;justify-content:space-between;
          padding:12px 16px;
          border-bottom:1px solid #e5e7eb;
          background:#ffffff;
        "
      >
        <h5
          class="modal-title"
          id="SuccessModalLabel"
          style="margin:0;display:flex;align-items:center;gap:10px;font-size:14px;font-weight:900;color:#111111;"
        >
          <span
            style="
              display:inline-flex;align-items:center;justify-content:center;
              width:26px;height:26px;
              border:1px solid #111111;border-radius:6px;
              background:#ffffff;
            "
            title="Success"
          >
            <i class="ri-checkbox-circle-line" aria-hidden="true" style="font-size:16px;color:#111111;line-height:1;"></i>
          </span>
          <span>Success</span>
        </h5>

        <button
          type="button"
          class="close"
          data-dismiss="modal"
          aria-label="Close"
          style="
            background:transparent;border:none;cursor:pointer;
            padding:6px 8px;border-radius:8px;
            font-size:18px;line-height:1;color:#111111;
          "
          onmouseover="this.style.background=\'#f3f4f6\'"
          onmouseout="this.style.background=\'transparent\'"
        >
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <!-- Body -->
      <div class="modal-body" style="padding:14px 16px;color:#111111;line-height:1.55;">
        <div style="margin-bottom:8px;">
          Coursework and examination marks for this class have been successfully posted.
        </div>
        <div style="font-weight:700;">
          Would you like to export a copy of the posted marks?
        </div>
      </div>

      <!-- Footer -->
      <div
        class="modal-footer"
        style="
          display:flex;align-items:center;justify-content:flex-end;gap:10px;
          padding:12px 16px;
          border-top:1px solid #e5e7eb;
          background:#ffffff;
        "
      >
        <!-- YES -->
        <button
          type="button"
          onclick="downloadCarryPDF(); setTimeout(goBack, 500);"
          style="
            display:inline-flex;align-items:center;gap:8px;
            padding:8px 14px;
            background:#111111;
            color:#ffffff;
            border:1px solid #111111;
            border-radius:8px;
            cursor:pointer;
            font-size:12px;font-weight:900;
          "
          onmouseover="this.style.background=\'#000000\'"
          onmouseout="this.style.background=\'#111111\'"
        >
          <i class="ri-download-2-line" aria-hidden="true" style="font-size:16px;color:#ffffff;line-height:1;"></i>
          <span style="letter-spacing:0.3px;">Yes</span>
        </button>

        <!-- NO -->
        <button
          type="button"
          data-dismiss="modal"
          style="
            display:inline-flex;align-items:center;gap:8px;
            padding:8px 12px;
            background:transparent;
            border:none;
            border-radius:8px;
            cursor:pointer;
            font-size:12px;font-weight:900;
            color:#111111;
          "
          onmouseover="this.style.background=\'#f3f4f6\'"
          onmouseout="this.style.background=\'transparent\'"
        >
          <i class="ri-close-circle-line" aria-hidden="true" style="font-size:16px;color:#111111;line-height:1;"></i>
          <span style="letter-spacing:0.3px;">No</span>
        </button>
      </div>
    </div>
  </div>
</div>



<div
  class="modal fade"
  id="confirm-carry"
  tabindex="-1"
  role="dialog"
  aria-labelledby="exampleModalCenterTitle"
  aria-hidden="true"
>
  <div class="modal-dialog modal-md" role="document" style="z-index:1050;">
    <div class="modal-content nust-modal-content">
      <div class="modal-header nust-modal-header">
        <h5 class="modal-title nust-modal-title" id="confirms">
          <i class="ri-edit-box-line"></i>
          <span>Post Carry Assessment</span>
        </h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body">
        <p id="modal-par">
          You are about to post both <strong>coursework</strong> and
          <strong>examination</strong> marks for this class.
          Once the results are posted, they will be <strong>view-only</strong> in the system
          and no further changes will be allowed.
        </p>
      </div>

      <div class="modal-footer nust-modal-footer">
        <button
          type="button"
          class="btn btn-secondary nust-btn"
          data-dismiss="modal"
        >
          <i class="ri-close-line"></i>
          <span>Cancel</span>
        </button>

        <button
          id="ok-postmodal-carry"
          type="button"
          class="btn btn-success nust-btn nust-btn-primary"
          data-dismiss="modal"
        >
          <i class="ri-upload-2-line"></i>
          <span>Post</span>
        </button>
      </div>
    </div>
  </div>
</div>




		<style>
		.tab-container {
			position: fixed; /* Fixed positioning relative to the viewport */
			bottom: 0; /* At the bottom of the viewport */
			right: 0; /* At the right of the viewport */
			margin: 0; /* No margin */
			padding: 10px; /* Padding inside the container */
		  }
		
		  .tab {
			background-color: transparent; /* Transparent background */
			color: white;
			border: 1px solid white; /* White border */
			border-radius: 15px; /* Rounded corners */
			padding: 10px 20px;
			
			font-size: 14px; /* Adjust as needed */
			transition: background-color 0.3s; /* Smooth background color transition on hover */
		  }
		
		  .tab:hover {
			background-color: rgba(255, 255, 255, 0.1); /* Slightly white transparent background on hover */
		  }
		
		  .tab-icon {
			background-color: transparent; /* Transparent background */
			color: white;
			border-radius: 50%; /* Circular icon */
			padding: 10px; /* Adjust as needed */
			display: inline-flex; /* For centering icon content */
			align-items: center;
			justify-content: center;
			margin-left: 10px; /* Space between text and icon */
		  }
		
		  .tab-icon i {
			font-size: 20px; /* Adjust as needed */
		  }

		active {
			background-color: black !important;
			border: 2px solid black !important;
			border-radius: 8px;
			font-family: Calibri, Candara, Segoe, Segoe UI, Optima, Arial, sans-serif;
			font-size: 12px;
			font-weight: bold;
			color: white !important;
		  }
		  
		  .nav-link.disabled {
			opacity: 0.5;
			pointer-events: none;
		  }


  /* Apply custom style to DataTable */
  #marks_capture_table {
    border-width: 0;
    font-family: sans serif, Calibri, Candara, Segoe, Segoe UI, Optima, Arial, sans-serif;
    font-size: 10px;
  }

  /* Add this to your CSS */
.focused {
    border: none;
	background-color: transparent;
	outline: none;
}


  #marks_capture_table td {
    max-width: 10px; /* Adjust this value to control cell width */
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
  }

    .zebra-striped tr:nth-child(even) {
        background-color: #f2f2f2; /* This is light grey */
    }

    .zebra-striped tr:nth-child(odd) {
        background-color: #ffffff; /* This is white */
    }
	#marks_capture_table tr.selected {
		background-color: black;
		color: white;
	}
	
	#marks_capture_table tr.selected td:first-child::before {
		content: "\270E"; /* Unicode for a pencil (edit icon) */
		padding-right: 5px;
	}



	

</style>
<style>
<style>

.nav-tabs {
    position: fixed; /* Fixed position */
    bottom: 20px; /* Positioned at the bottom */
    right: 20px; /* Positioned to the right */
    background: rgba(255, 255, 255, 0.5); /* Semi-transparent background */
    border-radius: 25px; /* Rounded corners */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Box shadow for floating effect */
    padding: 10px; /* Padding inside the nav container */
  }

  .nav-item {
    margin-bottom: 0; /* Remove negative margin */
  }

  .nav-link {
    background-color: transparent;
    border: none;
    color: #333; /* Text color */
    margin-right: 5px; /* Space between tabs */
    padding: 8px 16px; /* Padding inside the tabs */
    border-radius: 15px; /* Rounded corners */
    transition: all 0.3s ease; /* Smooth transition for hover effects */
  }

  .nav-link.active,
  .nav-link:hover {
    background: rgba(255, 255, 255, 0.75); /* More opaque background on active/hover */
    color: #000; /* Text color for active/hover state */
  }

  .tab-icon {
    border-radius: 50%; /* Circular icon background */
    padding: 8px; /* Padding inside the icon background */
    margin-right: 10px; /* Space between icon and text */
    background-color: #3498db; /* Icon background color */
    display: inline-flex; /* Align icon and text */
    justify-content: center;
    align-items: center;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); /* Subtle shadow for depth */
  }

  .fa-icon {
    color: white; /* Icon color */
    font-size: 1.2em; /* Icon size */
  }


  .tab-container {
    position: fixed; /* Fixed positioning relative to the viewport */
    bottom: 10px; /* At the bottom of the viewport */
    right: 10px; /* At the right of the viewport */
  }

  .tab {
    background-color: transparent; /* Transparent background */
    color: white; /* White text color */
    border: 1px solid white; /* White border */
    border-radius: 20px; /* Rounded corners */
    padding: 10px 20px; /* Padding inside the button */
    margin: 0 10px; /* Margin between buttons */
    font-size: 14px; /* Font size of the text */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Subtle shadow for depth */
    transition: background-color 0.3s; /* Transition for hover effect */
  }

  .tab:hover {
    background-color: rgba(255, 255, 255, 0.2); /* Slightly visible background on hover */
  }

  .tab-icon {
    display: inline-block;
    margin-right: 5px; /* Space between icon and text */
  }

  .fa-icon {
    background-color: #3498db; /* Blue background for the icon */
    border-radius: 50%; /* Circular icon */
    padding: 8px; /* Padding inside the icon */
    margin-right: 10px; /* Space after the icon */
    color: white; /* Icon color */
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); /* Shadow for the icon */
  }
</style>
<style>
  .floating-tabs {
    position: fixed; 
    bottom: 20px;
    right: 20px;
    display: flex; 
    flex-direction: column; 
    align-items: flex-end; 
  }

  .tab {
    background-color: #f8f9fa; 
    color: #007bff; /* Blue  */
    border: 1px solid #dee2e6; /* Slim border */
    border-radius: 15px; /* Rounded corners */
    padding: 8px 16px; /* Padding inside the tab */
    margin-bottom: 10px; /* Space between tabs */
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2); /* Shadow for floating effect */
    display: flex; /* To align icon and text */
    align-items: center; /* Vertical alignment */
    font-size: 11px; /* Font size of the text */
    text-decoration: none; /* Remove underline from links */
    transition: box-shadow 0.3s; /* Transition for shadow effect */
  }

  .tab:hover {
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3); /* Larger shadow on hover for lifting effect */
  }

  .tab-icon {
    background-color: #3498db; /* Blue background for the icon */
    border-radius: 50%; /* Circular icon */
    padding: 8px; /* Padding inside the icon */
    color: white; /* Icon color */
    margin-right: 8px; /* Space between icon and text */
  }
</style>





		<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
			<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ho+j7jyWK8fNQe+A12Hb8AhRq26LrZ/JpcUGGOn+Y7RsweNrtN/tE3MoK7ZeZDyx" crossorigin="anonymous"></script>
			<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
			<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.3.1/jspdf.umd.min.js"></script>
			<!-- Include the autoTable plugin -->
			<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.14/jspdf.plugin.autotable.min.js"></script>

				
<script>


			
							
			 $(document).ready(function() {

			 
                //variables declarred
				var selectedRow = null; // To store the selected row
				 var totalCourseworkItems = '. $totalcourseworkitems .';
				 var markingscheme = '.$markingschemeDataJSON.';
				 var courserowmark = []; // Array to store marks
				 var assessmentData = '. $assessmentDataJSON .';
				 var columns = '.$columnsJson.';
				 var table = $("#marks_capture_table");
				 var oldrowcache = [];
				 var existingData;
				 var selected_assessment_id;
				 var confValue = false;// This variable will store the value for conf, if its clicked or not
				 var examWeight = -1;
				 var assessmentWeight = -1;
				 var publicCW = 0;
				 
				 var is_recalculate = 0; //we do the recalculte

				 var lecturer_course_id = '.$selectedclass.';
				 var course_id = '.$course_id.';
                 var semester = '.$semester.';
                 var part = '.$yearofstudy.';

				 examWeight = '.$examWeight.';
				 assessmentWeight = "'.$assessmentWeight.'";
				 otherExamWeight = "'.$otherExam.'";
				 
				
				
				console.log(assessmentWeight);
			
		
		
		
		//handle posting results buttons
		//handle post normal exams
				$(\'#postresultsbtn\').click(function() {
					
					$(\'#confirm\').modal(\'show\');
					
				});




		//handle post coursework onlyy...
				$(\'#postcourseworkbtn\').click(function() {
					
					$(\'#confirm-courseworkonly\').modal(\'show\');
					
				});
				
	
		
		
				
				
				//handle post carry exams
				$(\'#postcarrybtn\').click(function() {
					
					$(\'#confirm-carry\').modal(\'show\');
					
				});
				
				
				
				// Consolidated handler for posting data when the OK button in the modal is clicked
    $(\'#ok-postmodal\').on(\'click\', function() {
        var studentNumbers = [];
        $(\'#marks_capture_table tbody tr\').each(function() {
            var regNumber = $(this).find(\'td:eq(1)\').text().trim();
            studentNumbers.push(regNumber);
        });
          postAssessment(studentNumbers, \'Regular Exam\');
    });


			
				//for posting coursework only
    $(\'#ok-postcourseworkmodal\').on(\'click\', function() {
        var studentNumbers = [];
        $(\'#marks_capture_table tbody tr\').each(function() {
            var regNumber = $(this).find(\'td:eq(1)\').text().trim();
            studentNumbers.push(regNumber);
        });
          postCourseworkonly(studentNumbers, \'Regular Exam\');
    });




				//handles for carry
    $(\'#ok-postmodal-carry\').on(\'click\', function() {
        var studentNumbers = [];
        $(\'#carry_students_table tbody tr\').each(function() {
            var regNumber = $(this).find(\'td:eq(1)\').text().trim();
            studentNumbers.push(regNumber);
        });
          postAssessment(studentNumbers, \'Carry Exam\' );
        
      });
				
				
function postAssessment(studentNumbers, examtype) {

        // Prepare XMLHttpRequest
        var xhr = new XMLHttpRequest();
	xhr.open("POST", "'.$this->core->conf['conf']['path'].'/api/postassessment", true);
	xhr.setRequestHeader("Content-Type", "application/json");

        xhr.onreadystatechange = function() {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                console.log("AJAX request state: Done");
                if (xhr.status === 200) {
                    var response = JSON.parse(xhr.responseText);
                    console.log("Server response", response);

                    if (response.status > 0) {
							//here we chose the right modal to download
							if (examtype === \'Regular Exam\') {
							
								$(\'#SuccessModal\').modal(\'show\');
							} else if (examtype === \'Carry Exam\') {
							
								$(\'#SuccessModalCarry\').modal(\'show\');
							}
							
                    } else {
                        $(\'#ErrorModal\').modal(\'show\');
                    }
                } else {
                    console.log("Failed to send data: Status", xhr.status);
                    $(\'#ErrorModal\').modal(\'show\');
                }
            }
        };

       // Send the JSON data
		var jsonData = JSON.stringify({studentNumbers: studentNumbers, lecturer_course_id: '.$selectedclass.', examtype:examtype});
		xhr.send(jsonData);

        $(\'#confirm\').modal(\'hide\');  // Optionally close the modal if needed
  }


				

  

  		
function postCourseworkonly(studentNumbers, examtype) {

  var xhr = new XMLHttpRequest();
	xhr.open("POST", "'.$this->core->conf['conf']['path'].'/api/postcourseworkonly", true);
	xhr.setRequestHeader("Content-Type", "application/json");

        xhr.onreadystatechange = function() {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                console.log("AJAX request state: Done");
                if (xhr.status === 200) {
                    var response = JSON.parse(xhr.responseText);
                    console.log("Server response", response);

                    if (response.status > 0) {
							//here we chose the right modal to download
							if (examtype === \'Regular Exam\') {
              console.log("success oursework only");
							
								$(\'#CourseAssessOnlySuccessModal\').modal(\'show\');
							
               
              
                } else if (examtype === \'Carry Exam\') {
							
								$(\'#SuccessModalCarry\').modal(\'show\');

                       


							}
							
                    } else {
                        $(\'#ErrorModal\').modal(\'show\');
                    }
                } else {
                    console.log("Failed to send data: Status", xhr.status);
                    $(\'#ErrorModal\').modal(\'show\');
                }
            }
        };

       // Send the JSON data
		var jsonData = JSON.stringify({studentNumbers: studentNumbers, lecturer_course_id: '.$selectedclass.', examtype:examtype});
		xhr.send(jsonData);

        $(\'#confirm-courseworkonly\').modal(\'hide\'); 
 

        
  }


				
				
				
				
				
				
				
				//reload
				
				 $(document).on("click", "#postingcourseworkdone", function (e) {
              e.preventDefault();
              window.location.reload(); 
            });
				
				
				
				
				
   //this lauches the carry student add modal
		
				$(\'#missing-students\').click(function() {
					$(\'#add_carry_student\').modal(\'show\');
					
				});
		
		
			//end modal
			
			
			






      
			  
	
	
	$(\'#studentNumberInput\').on(\'input\', function() {
    var input = $(this);
    var value = input.val();
    var isTenDigits = value.length === 10;
    var isNineDigitsPlusLetter = value.length === 9 && isNaN(value.charAt(8));

     if (isTenDigits || isNineDigitsPlusLetter) {
        // Append the student number within a span, including a close button (\'X\') inside another span for removal.
        $(\'#studentNumbersContainer\').append(\'<span style="padding: 5px; margin-right: 5px; background-color: #e6e6e6; border-radius: 4px;"><span class="studentNumber">\' + value + \'</span><span class="remove" style="margin-left: 5px; cursor: pointer; color: red;">X</span></span>\');
        
        input.val(\'\'); 
    }
});
        
$(\'#studentNumbersContainer\').on(\'click\', \'.remove\', function() {
    $(this).parent().remove();
});		
						
						
										
						
						  $(\'#submitStudentNumbers\').on(\'click\', function() {
             					
             					var student_numbers = [];

								// Iterate over each .studentNumber span in the \'studentNumbersContainer\'.
								$(\'#studentNumbersContainer .studentNumber\').each(function() {
									// Get the text from each student number span and trim any extra whitespace.
									var student_number = $(this).text().trim();
							
									// Add the extracted student number to the array.
									student_numbers.push(student_number);
								});
					

         console.log(student_numbers); // For demonstration, replace with actual submission code
     
         
     	// Prepare XMLHttpRequest
					var xhr = new XMLHttpRequest();
					xhr.open("POST", "'.$this->core->conf['conf']['path'].'/api/addcarrystudents", true);
					xhr.setRequestHeader("Content-Type", "application/json");
			
					xhr.onreadystatechange = function () {
						  if (xhr.readyState === XMLHttpRequest.DONE)
							
							if (xhr.status === 200) {
									console.log("here");
									var response = JSON.parse(xhr.responseText);
									switch (response.status) {
										case 1: // All students successfully added
											$(\'#cary-success-modal\').modal(\'show\');
											$(\'#cary-success-modal .modal-body\').text(\'All students added. Select the "Carry Students" tab and start capturing marks.\');
											break;
										case 2: // Some students added
											$(\'#carry-errorModal\').modal(\'show\');
											$(\'#carry-errorModal .modal-body\').text(\'Some students were not added successfully. Please try again.\');
											break;
										default: // No students added, show error
											$(\'#carry-errorModal\').modal(\'show\');
											$(\'#carry-errorModal .modal-body\').text(\'No students were added. Please try again.\');
											break;
									}
								} else {
									$(\'#carry-errorModal\').modal(\'show\');
									$(\'#carry-errorModal .modal-body\').text(\'An error occurred. Please try again.\');
								}

								
															
							};

			
					
					var jsonData = JSON.stringify({
													studentNumbers: student_numbers,
													lecturer_course_id: '.$selectedclass.',
													course_code: '.$course_id.',
													exam_sitting_code: "S1-2024",
													period: '.$periodID.',												
													semester: '.$semester.',
													part: '.$yearofstudy.'
												});
					
					
						console.log("Carry data to be posted", jsonData);

					xhr.send(jsonData);

     
      });
						

//RELOAD PAGE AFTER MODAL
	$(\'#carry-success-modal\').on(\'hidden.bs.modal\', function () {
       // Set a flag in localStorage before refreshing
    localStorage.setItem(\'selectCarryTab\', \'true\');
    window.location.reload(); // Refresh the page

});

			/// end of carry handling
			
			
			
			
					

 
				 // Here we Check if examWeight and assessmentWeight are not populated.
				 if ( !assessmentWeight || examWeight == -1|| assessmentWeight == -1 ) {
					alert("no exam weight");
					 // Clear the table and show the message
					 $(\'#marks_capture_table\').html(
						 \'<thead>\' +
							 \'<tr><th>Message</th></tr>\' +
						 \'</thead>\' +
						 \'<tbody>\' +
							 \'<tr><td>No content allowed for mark upload. Exam assessment weight for this course is not set or no assessment has been created for this course.</td></tr>\' +
						 \'</tbody>\'
					 );
				 }


			

//$(".nav-link").click(function() {
//  $(".nav-link").removeClass("active").css({"background-color": "", "border-color": "", "color": ""});
//  $(this).addClass("active").css({"background-color": "#E0E0E0", "border-color": "#E0E0E0", "color": "blue"});
//});


//$(".nav-link").click(function() {
//    // Remove all inline styles
//    $(".nav-link").removeClass("active").removeAttr("style");
//
//    // Add styles only for the clicked element
//    $(this).addClass("active").css({
//        "border-bottom-width": "2px",
//        "border-bottom-style": "solid",
//        "border-bottom-color": "#E0E0E0"
//    });
//
//    // Remove bottom border from other elements
//    $(".nav-link").not(this).css("border-bottom", "");
//});


//
//              ////newly added
//				  $("#marks_capture_table tr").click(function() {
//					// Remove the "selected" class from all rows
//					$("#marks_capture_table tr").removeClass("selected");
//			
//					// Add the "selected" class to the clicked row
//					$(this).addClass("selected");
//				});


	
	
	 $(".marks_table tr").on("click", function() {
        // Reset the styles for all rows in the table containing the clicked row
        var $table = $(this).closest(\'.marks_table\');
        $table.find(\'tr\').css({
            "border-bottom": "1px solid #000",  // Default bottom border
            "border-top": "1px solid #000",    // Default top border
            "font-weight": "normal"            // Default font weight
        });

        // Apply custom styling to the clicked row
        $(this).css({
            "border-bottom": "1.5px solid #D3D3D3", // Thicker bottom border
            "border-top": "1.5px solid #D3D3D3",   // Thicker top border
            "font-weight": "bold"                  // Bold font weight
        });
    });
	
	
	
	
	 // Handle mouse leave event for all tables with the class \'marks_table\'
    $(".marks_table").mouseleave(function() {
        // Reset the styles for all rows in this table
        $(this).find(\'tr\').css({
            "border-top": "1px solid #000",   // Reset to the default top border style
            "border-bottom": "1px solid #000", // Reset to the default thin black bottom border
            "font-weight": "normal"           // Reset font weight to normal
        });
    });
	  
$(".marks_table tbody").on("click", ".editable", function() {
    var $this = $(this);
    var originalContent = $this.text();
    $this.attr("contenteditable", "true").focus();

    // Function to select all text within the contenteditable element
    function selectText(element) {
        if (document.body.createTextRange) {
            // For Internet Explorer
            var range = document.body.createTextRange();
            range.moveToElementText(element);
            range.select();
        } else if (window.getSelection) {
            // For other browsers
            var selection = window.getSelection();        
            var range = document.createRange();
            range.selectNodeContents(element);
            selection.removeAllRanges();
            selection.addRange(range);
        }
    }

    // Select the text inside the clicked cell
    selectText(this);
});







						
														
							 // Handling the blur event on editable cells
									$(".marks_table").on("blur", ".editable", function() {
										$(this).attr("contenteditable", "false");
									});
								
									// Handling arrow key navigation for editable cells
									$(".marks_table").on("keydown", ".editable[contenteditable=\'true\']", function(e) {
										var $this = $(this);
										var index = $this.index();
										var $next;
								
										switch (e.which) {
											case 37: // Left arrow
												$next = $this.prevAll(".editable").first();
												break;
											case 38: // Up arrow
												$next = $this.closest(\'tr\').prev(\'tr\').find(\'td\').eq(index);
												break;
											case 39: // Right arrow
												$next = $this.nextAll(".editable").first();
												break;
											case 40: // Down arrow
												$next = $this.closest(\'tr\').next(\'tr\').find(\'td\').eq(index);
												break;
										}
								
										if ($next && $next.length && $next.hasClass(\'editable\')) {
											$this.blur().attr("contenteditable", "false");
											$next.attr("contenteditable", "true").focus();
										}
								
										e.preventDefault(); // Prevent default action of the arrow keys
									});
						
															
									
									$(document).on("click", ".marks_table .conf-checkbox", function(e) {
										e.stopPropagation(); // Prevent triggering clicks from parent elements
										var $this = $(this);
									
										// Check if a checkbox already exists and toggle its state
										if (!$this.find(\'input[type="checkbox"]\').length) {
											var checkboxHtml = \'<div class="custom-control custom-checkbox">\' +
												\'<input type="checkbox" class="custom-control-input conf-checkbox" checked>\' +
												\'<label class="custom-control-label"></label>\' +
												\'</div>\';
											$this.html(checkboxHtml);
											confValue = true;
										} else {
											$this.empty();
											confValue = false;
										}
									});
									



						


						
						
						
						
						
										
			//not modified after carry
				// Change the font size of the DataTable using jQuery
				$("#marks_capture_table").css({
					"font-size": "11px",
					"border": "0px solid #ccc",
					"border-width": "0",
					"font-family": "Calibri, Candara, Segoe, Segoe UI, Optima, Arial, sans-serif",
					"height": "50%", // Set the table height
					"padding-top": "0", // Corrected syntax
					"padding-bottom": "0" // Corrected syntax
				});
			
				$("#marks_capture_table tbody").on("click", "td.column-class input[type= \'checkbox\']", function(event) {
					
				});
			
			

			

			
			$(".marks_table").on("click", "tbody td.editable", function() {
    var originalValue = $(this).text();
    var focusedCell = $(this);
    focusedCell.html(`<input type="text" value="${originalValue}" style="width: 100%; border: none; outline: none;" />`);

    var input = focusedCell.find("input");
    input.focus();

    // Position cursor at the end of the input value
    var length = input.val().length;
    input[0].setSelectionRange(length, length);

    focusedCell.addClass("focused"); // Add class to remove border when cell is focused

    // Event listener to prevent propagation when input is clicked
    input.on("click", function(e) {
        if (focusedCell.hasClass("focused")) {
            e.stopPropagation();
        }
    });

    // Handle arrow key navigation within the table
    input.on("keydown", function(event) {
        var currentRowIndex = focusedCell.closest("tr").index();
        var currentColIndex = focusedCell.index();
        var targetRow, targetCell;

        switch (event.keyCode) {
            case 37: // Left arrow
                targetCell = focusedCell.prevAll(\'td.editable:first\');
                break;
            case 39: // Right arrow
                targetCell = focusedCell.nextAll(\'td.editable:first\');
                break;
            case 38: // Up arrow
                targetRow = focusedCell.closest("tr").prevAll().find(\'td.editable\').parent(\'tr:not(:has(td[colspan])):first\');
                targetCell = targetRow.find(\'td\').eq(currentColIndex);
                break;
            case 40: // Down arrow
                targetRow = focusedCell.closest("tr").nextAll().find(\'td.editable\').parent(\'tr:not(:has(td[colspan])):first\');
                targetCell = targetRow.find(\'td\').eq(currentColIndex);
                break;
        }

        if (targetCell && targetCell.length > 0 && targetCell.hasClass("editable")) {
            focusedCell.find(\'input\').blur();
            focusedCell.removeClass("focused");
            targetCell.click();
        }

        if (event.keyCode === 9 || event.keyCode === 13) { // Tab or Enter key
            event.preventDefault();
            navigateToNextEditableCell(focusedCell);
        }
    });
});

function navigateToNextEditableCell(focusedCell) {
    var nextCell = focusedCell.closest("tr").find("td").eq(focusedCell.index() + 1);
    var nextRow = focusedCell.closest("tr").next();
    
    if (nextCell.length > 0 && nextCell.hasClass("editable")) {
        nextCell.click();
        var inputElement = nextCell.find("input");
        if (inputElement.length > 0) {
            inputElement[0].select(); // Focus and select text inside the input element
        }
    } else if (nextRow.length > 0) {
        var firstEditableCell = nextRow.find("td.editable").eq(0);
        if (firstEditableCell.length > 0) {
            firstEditableCell.click();
            var inputElement = firstEditableCell.find("input");
            if (inputElement.length > 0) {
                inputElement[0].select();
            }
        }
    }
}

	
	
 var lastIndexClicked = null;
    var originalData = [];
    var existing = [];
    var initialdata = [];
    var currentRowData = null;
    var currentRowIndex = null;
    var rowchange = false;
    var rowchanged = false;
    var newrowcheck = false;
    var dataChanged = false;
    var lastClickedCell = null; // This variable will store the last clicked cell

    var storedRowData = {};

    // Apply event handling to all tables with class \'marks_table\'
    $(".marks_table").on("click", "tr", function(event) {
        var $currentRow = $(this); // The row that was clicked
        var originalValue = $currentRow.text();
        $currentRow.attr(\'data-original-value\', originalValue); // Store original value
        dataChanged = false;
        newrowcheck = false;

        // Extract the text from each cell in the row into an array
        var rowData = $currentRow.find(\'td\').map(function() {
            return $(this).text();
        }).get();
        
        console.log("row data at change ", rowData);

        var currentCell = $(event.target); // Current clicked cell

        // Check if the same cell is clicked
        if (lastClickedCell && currentCell.is(lastClickedCell)) {
            console.log("Same cell clicked again.");
            rowchange = false;
            return;
        } else if (lastClickedCell && !currentCell.closest("tr").is(lastClickedCell.closest("tr"))) {
            rowchange = true;
            console.log("Different cell clicked.");
        }

        lastClickedCell = currentCell; // Update the last clicked cell to the current one

        var rowIndex = $currentRow.index(); // Get the index of the clicked row

        // Check if we have stored data for this row already
        if (!storedRowData[rowIndex]) {
            console.log("Row clicked for the first time.");
            storedRowData[rowIndex] = rowData.slice(); // Clone the array
            console.log("Stored data", storedRowData[rowIndex]);
        } else {
            console.log("Row clicked again.");
            var existingData = storedRowData[rowIndex];
            console.log("Existing data", existingData);
        }

        // Check if it\'s a different row or if no row was clicked before
        if (currentRowIndex !== rowIndex) {
            console.log("Different row clicked or first time clicking any row.");
            currentRowData = rowData; // This should reflect the edited data if any.
            currentRowIndex = rowIndex;
            rowChanged = true;
        } else {
            console.log("Same row clicked again. Not updating stored data.");
        }

        // If initialdata is null or different from the rowData
        if (!initialdata || JSON.stringify(initialdata) !== JSON.stringify(rowData)) {
            console.log("Row data has changed!");
            dataChanged = true;
            initialdata = rowData;
        } else {
            console.log("No Row data has changed!");
        }

        // Check if it\'s a different row than the last clicked one
        if (lastIndexClicked !== rowIndex) {
            newrowcheck = true;
            originalData = rowData;
            console.log("New row clicked!", rowIndex, lastIndexClicked); // Log message
            lastIndexClicked = rowIndex;
        }
    });

		
	
	
	
	
	
	
	
		let previousRow = null; // This will help us keep track of the last row interacted with
	
			
		// Track the previous data when the input is focused
			$(".marks_table").on("focus", "input", function() {
				var row = $(this).closest("tr");
				let serializedData = row.find("input").serialize();
				console.log("Setting original data: ", serializedData); // Debugging log
				row.data("originalData", serializedData);
			});
		
					
		$(".marks_table").on("blur", "input", function() {
			var inputElement = this;
			var table = $(this).closest("table"); 
			var tableId = table.attr(\'id\'); 
			handleRowChange(inputElement, saveMethod, tableId); 
		});
		
	   //the function to call the button
		
           			
		$("#recompute-grades-button").on("click", function () {
          console.log("Gods hand");
          alert();
                processAllRowsInMarksTable();
            });
		
		 
		//end of the call function

				
// This handles the recalculate button on the system
function processAllRowsInMarksTable() {


    console.log("Starting processAllRowsInMarksTable");

    const $table = $("#marks_capture_table");
    console.log("Selected table:", $table.attr("id"));

    const $rows = $table.find("tbody tr");
    console.log("Number of rows found:", $rows.length);

    // Show modal with loading and progress bar
    $("#processingModal").modal("show");
    console.log("Modal shown: #processingModal");

    $rows.each(function (index) {
        const $row = $(this);
        const tableId = $table.attr("id");

        console.log(` Processing row index: ${index}`);

        //  Call handleRowChange for the entire row
        handleRowChange($row, saveMethod, tableId);

        console.log("handleRowChange called for row");

        // Update modal progress
        const regno = $row.find("td").eq(1).text().trim(); // assuming RegNo is in column 1
        console.log("Current student number:", regno);

        let percent = Math.round(((index + 1) / $rows.length) * 100);
        console.log(`Progress: ${percent}%`);

        $("#current-student").text(regno);
        console.log("📤 Updated current student in modal");

        $("#progress-bar").css("width", percent + "%").attr("aria-valuenow", percent);
        console.log("Updated progress bar width");

        $("#progress-text").text(percent + "%");
        console.log("Updated progress text");
    });

    // Hide modal after processing all rows
    $("#processingModal").hide();
    console.log(" Modal hidden: #processingModal");
}


		


//
function handleRowChange(inputElement, callback, tableId) {
    console.log("handleRowChange");

   
    


    let selectedRow, table;

    // Check if inputElement is actually a <tr> (batch call)
    if ($(inputElement).prop("tagName") === "TR") {
        console.log("📌 Detected row element (TR) from batch call");
        selectedRow = $(inputElement);
        table = selectedRow.closest("table");
		 is_recalculate = 1;
    } else {
        console.log("📝 Detected input element from blur");
        selectedRow = $(inputElement).closest("tr");
        table = $(inputElement).closest("table");
		 is_recalculate = 0;
    }


	
    const tableName = tableId;
    
    
    

    if (selectedRow.length > 0) {
        var focusedCell = $(inputElement).closest("td");
        var rowData = selectedRow.find(\'td\').map(function() {
            return $(this).text();
        }).get();

        // Use the identified table\'s ID to construct the selector for finding headers
       // var cwindex = $("#" + tableName + " thead th#" + assessmentData[0].courseID).index();
        var cwindex = "null";
        
         if (assessmentWeight > 0) {
          cwindex = 3 + '.$totalcourseworkitems.';
         }
        var newValue = $(inputElement).val().trim();
        var isValid = validateInput(inputElement); // Implement this function based on your validation rules

        if (isValid)
         {
            focusedCell.text(newValue);
            var columnIndex = focusedCell.index();
            rowData[columnIndex] = newValue;
        } 
        else 
        {
            $(inputElement).val(\'\'); // Clear the input field
            focusedCell.empty(); // Clear the cell
        }

        // Convert to the markup-free and trimmed text
        var originalData = oldrowcache.map(item => stripHTML(item).trim());
        rowData = rowData.map(item => stripHTML(item).trim());

        var rowIndex = selectedRow.index();
        var hasChanged = !originalData.every(function(item, index) {
            return item === rowData[index];
        });

        var courserowmark = populateMarksArray(rowData, totalCourseworkItems);
        var overalCM = calculateOCM(courserowmark, assessmentData);

        // Use the table ID in all selectors within the table context
        let m = $("#" + tableName + " thead th#cw").index();
        let grade = $("#" + tableName + " thead th#grade").index();
        let remark = $("#" + tableName + " thead th#remark").index();
        let otherExamIndex = $("#" + tableName + " thead th#otherExam").index();
        var otherExamMark = otherExamIndex !== -1 ? parseFloat(rowData[otherExamIndex]) : null;

        let exam = $("#" + tableName + " thead th#exam").index();
        let mark = $("#" + tableName + " thead th#mark").index();
        let comment = $("#" + tableName + " thead th#comment").index();

        if (m !== -1) {
            const cell = selectedRow.find("td").eq(m);
            overalCM = Math.round(parseFloat(overalCM));
            console.log("overalCM 2024", overalCM);
            cell.text(overalCM.toString());
        }

       // rowData[cw] = overalCM;
       
        if (assessmentWeight > 0) {
        rowData[cw] = overalCM;
    } 
     
        publicCW = overalCM;

        var exammarkCaptured = parseFloat(rowData[exam]);
        // Existing logic with updated function calls
        if (exammarkCaptured == 0) {
            showInfoAlert("Please click CONF column if the exam mark = 0.")
                .then(() => {
                    confValue = true;
                    if (examWeight !== -1) {
                        continueHandleRowChange(selectedRow, rowData, cwindex, otherExamMark, exammarkCaptured, overalCM, mark, grade, remark, callback, tableName);
                    } else {
                        continueHandleRowChange(selectedRow, rowData, cwindex, exammarkCaptured, overalCM, mark, grade, remark, callback, tableName);
                    }
                });
        } else {
            if (examWeight !== -1) {
                continueHandleRowChange(selectedRow, rowData, cwindex, otherExamMark, exammarkCaptured, overalCM, mark, grade, remark, callback, tableName);
            } else {
                continueHandleRowChange(selectedRow, rowData, cwindex, exammarkCaptured, overalCM, mark, grade, remark, callback, tableName);
            }
        }
    }
}


function continueHandleRowChange(selectedRow, rowData, cwindex, otherExamMark = null, exammarkCaptured, overalCM, mark, grade, remark,  callback, tableId)
 {
    console.log("Continue Handling Row Change in Table:", tableId);  // Now includes table identifier in logs
    console.log("exammarkCaptured Conditions:", exammarkCaptured !== "", exammarkCaptured !== null, !isNaN(exammarkCaptured));

    let overalEX;

    if (exammarkCaptured === "" || exammarkCaptured == null) 
    {
        // Assuming no exam mark; calculate overall exam mark without an actual exam mark
        overalEX = Math.round(calculateOverallExamMarkNoExam(exammarkCaptured, examWeight, overalCM, assessmentWeight, otherExamMark, otherExamWeight));
    } else {
        // When exam mark exists, evaluate further based on otherExamMark conditions
        if (otherExamWeight !== -1 && !isNaN(otherExamWeight) && otherExamMark !== -1) {
            overalEX = Math.round(calculateOverallExamMarkWithOtherNoExam(exammarkCaptured, examWeight, otherExamMark, otherExamWeight, overalCM, assessmentWeight));
        } 
        else {
            if (otherExamMark !== -1) {
                overalEX = Math.round(calculateOverallExamMarkWithTwoExams(exammarkCaptured, examWeight, overalCM, assessmentWeight, otherExamMark, otherExamWeight));
            } 
            else {
                overalEX = Math.round(calculateOverallExamMarkWithOneExam(exammarkCaptured, examWeight, overalCM, assessmentWeight));
            }
        }
    }

    const markCell = selectedRow.find("td").eq(mark);
    markCell.text(overalEX.toString());

    let currentClassification = getClassification(overalEX);
    let currentRemark = getRemark(overalEX);

    selectedRow.find("td").eq(grade).text(currentClassification);
    selectedRow.find("td").eq(remark).text(currentRemark);

    rowData[mark] = overalEX;
    rowData[grade] = currentClassification;
    rowData[remark] = currentRemark;
    
      if (assessmentWeight > 0) {

    console.log("Updated Row Data CW Mark:", rowData[cw]);
                        }
    if (overalEX < 50) {
        selectedRow.find("td").css(\'color\', \'red\');
    }

   
    
  console.log("Callback type:", typeof callback);
if (typeof callback === "function") {
    console.log("Calling saveMethod with data:", rowData, cwindex, currentClassification, currentRemark, confValue, tableId);
    callback(rowData, cwindex, currentClassification, currentRemark, confValue, tableId);
} else
 {
    console.log("Callback is not a function:", callback);
    console.log("Save Method Called for Table:", tableId);

    var $table = $(\'#\' + tableId);  // Use the passed tableId to select the table
    var headerRow = $table.find("thead th");
    var examIndex = headerRow.filter("[id=\'exam\']").index();
    var otherExamIndex = headerRow.filter("[id=\'otherExam\']").index();
    var markIndex = headerRow.filter("[id=\'mark\']").index();
    var gradeIndex = headerRow.filter("[id=\'grade\']").index();
    var remarkIndex = headerRow.filter("[id=\'remark\']").index();
    var confIndex = headerRow.filter("[id=\'conf\']").index();
    var commentIndex = headerRow.filter("[id=\'comment\']").index();
    
     console.log("cwMarkIndex" ,cwMarkIndex);

    // Building an array of assessment marks for other coursework
    var assessmentmarks = [];
    for (let i = 4; i < cwMarkIndex; i++) {
        let value = parseFloat(rowData[i]);
        if (!isNaN(value)) {
            let columnId = $(headerRow[i]).attr("id");
            assessmentmarks.push({ id: columnId, value: value });
        }
    }
    
    console.log("cwMarkIndex" ,cwMarkIndex);

    // Compile the assessment data to be sent
    var assessmentdata = {
        studentNumber: rowData[1], // Assuming 2nd column is student number
        cwMark: rowData[cwMarkIndex],
        examMark: rowData[examIndex],
        otherExam: rowData[otherExamIndex],
        mark: rowData[markIndex],
        grade: rowData[gradeIndex],
        remark: remark,
        comment: rowData[commentIndex],
        conf: confZero, // Directly use the boolean flag
        assessmentmarks: assessmentmarks // Collection of assessment marks
    };

    console.log("Assessment marks Final:", assessmentmarks);
    console.log("Assessment Data to be posted:", assessmentdata);
    console.log("Assessment marks Final:", assessmentmarks);
    console.log("2024 Assessment Data to be posted:", assessmentdata);

    var xhr = new XMLHttpRequest();
		 	
		  xhr.setRequestHeader("Content-Type", "application/json");
	
		  console.log("UNSENT: ", xhr.status);
		  
		  xhr.open("POST", "'.$this->core->conf['conf']['path'].'/api/saveassessmentresults", true);
		  xhr.onreadystatechange = function () {
			
			if (xhr.readyState === XMLHttpRequest.OPENED) {
			  console.log("Request opened");
			} else if (xhr.readyState === XMLHttpRequest.HEADERS_RECEIVED) {
			  console.log("Headers received");
			} else if (xhr.readyState === XMLHttpRequest.LOADING) {
			  console.log("Response loading");
			} else if (xhr.readyState === XMLHttpRequest.DONE) {
			  if (xhr.status === 200) 
			  {
				
				console.log("Data from courses table sent successfully!");	
						 
				
			if (xhr.responseText !== null) {
			
							var data = JSON.parse(xhr.responseText);
							console.log("Insert log", data);
							return;
			}
				console.log("FRI responseText", xhr.responseText);
							
				
						}
					}
	
						};
				  
	
			var jsonData = JSON.stringify(assessmentdata);
			xhr.send(jsonData);
			
}
};
function saveMethod(rowData, cwMarkIndex, markclassification, remark, confZero, tableId) {
    console.log("Save Method Called for Table:", tableId);
    console.log("COURSE WORK INDEX:", cwMarkIndex);


    var $table = $(\'#\' + tableId);  // Use the passed tableId to select the table
    var headerRow = $table.find("thead th");
    var examIndex = headerRow.filter("[id=\'exam\']").index();
    var otherExamIndex = headerRow.filter("[id=\'otherExam\']").index();
    var markIndex = headerRow.filter("[id=\'mark\']").index();
    var gradeIndex = headerRow.filter("[id=\'grade\']").index();
    var remarkIndex = headerRow.filter("[id=\'remark\']").index();
    var confIndex = headerRow.filter("[id=\'conf\']").index();
    var commentIndex = headerRow.filter("[id=\'comment\']").index();
    var ocwindex = headerRow.filter("[id=\'cw\']").index();
    var commentIndex = headerRow.filter("[id=\'comment\']").index();
    var markattindex  = headerRow.filter("[id=\'mark\']").index();
    
    var examtype = "Regular Exam"
		  
		if (tableId === "carry_students_table") {
			examtype = "Carry Exam";
		}  

    // Building an array of assessment marks for other coursework
    var assessmentmarks = [];
    for (let i = 4; i  <  cwMarkIndex + 1; i++) {

        let value = parseFloat(rowData[i]);
        if (!isNaN(value)) {
            let columnId = $(headerRow[i]).attr("id");
            assessmentmarks.push({ id: columnId, value: value });
        }
    }

    // Compile the assessment data to be sent
    var assessmentdata = {
        studentNumber: rowData[1], // Assuming 2nd column is student number
        cwMark: rowData[cwMarkIndex],
        examMark: rowData[examIndex],
        otherExam: rowData[otherExamIndex],
        markatt: rowData[markattindex],
        grade: rowData[gradeIndex],
        remark: remark,
        comment: rowData[commentIndex],
  		markclassification: markclassification,
		coursecode:"'.$coursecode.'",
		part:"'.$yearofstudy.'",
		semester:"'.$semester.'",
		regulation:"'.$progcode.'",
		courselectureid:"'.$selectedclass.'",
		examsittingcode: "S1-2024", 
    examYear: "'.$academicyear.'", 
  	examtype:examtype,
		ocw : rowData[ocwindex],
		conf : confZero,
		classCode:"'.$progcode.'",
		examWeight:'.$examWeight.',
		publicCW:publicCW,
		is_recalculate:is_recalculate,
    assessmentWeight : '.$assessmentWeight.',
        
        
        
        
       //these can be multiple assessmentmarks
        assessmentmarks: assessmentmarks // Collection of assessment marks
    };

    console.log("Assessment marks Final:", assessmentmarks);
    console.log("2024 Assessment Data to be posted:", assessmentdata);

    	  var xhr = new XMLHttpRequest();
    	  console.log("UNSENT: ", xhr.status);
    	   xhr.open("POST", "", true);
		  xhr.setRequestHeader("Content-Type", "application/json");		  
		  xhr.open("POST", "'.$this->core->conf['conf']['path'].'/api/saveassessmentresults", true);
		  xhr.onreadystatechange = function () {
			
			if (xhr.readyState === XMLHttpRequest.OPENED) {
			  console.log("Request opened");
			} else if (xhr.readyState === XMLHttpRequest.HEADERS_RECEIVED) {
			  console.log("Headers received");
			} else if (xhr.readyState === XMLHttpRequest.LOADING) {
			  console.log("Response loading");
			} else if (xhr.readyState === XMLHttpRequest.DONE) {
			  if (xhr.status === 200) 
			  {
				
				
				
				console.log("Data from courses table sent successfully!");	
						 
				
			if (xhr.responseText !== null) {
			
							var data = JSON.parse(xhr.responseText);
							console.log("Insert log", data);
							return;
			}
				console.log("FRI responseText", xhr.responseText);
							
				
						}
					}
	
						};
				  
	
			var jsonData = JSON.stringify(assessmentdata);
			xhr.send(jsonData);
			
}



//	validate input	
function validateInput(inputElement) {
    // Wrap the input element with jQuery
    var $input = $(inputElement);

    // Find the closest parent table to ensure the validation is context-specific
    var $table = $input.closest(\'table.marks_table\');

    // Get the index of the td relative to its siblings
    var cellIndex = $input.closest(\'td\').index();

    // Find the corresponding th element within the same table using the index
    var columnName = $table.find(\'thead th\').eq(cellIndex).attr(\'datatype\');
    var totalMark = $table.find(\'thead th\').eq(cellIndex).attr(\'data-total-mark\');

    // Get the value from the input element
    var value = $input.val();

    // Handle non-numeric columns specifically
    if (columnName === "string") {
        return validateNonNumericInput(value);
    } else {
        // Replace comma with dot for decimal numbers and trim white spaces
        value = value.replace(\',\', \'.\').trim();

        // Validate numeric input
        if (isNaN(value)) {
            showValidationAlert("Please enter a valid number.");
            return false;
        }

        var numericValue = parseFloat(value);

        // Check for negative values
        if (numericValue < 0) {
            alert("Marks cannot be negative.");
            return false;
        }

        // Validate against the maximum allowable mark
        if (totalMark && numericValue > parseFloat(totalMark)) {
            showValidationAlert("Marks cannot exceed " + totalMark + ".");
            return false;
        }

        return true;
    }
}



//this function validates the non number column so if anything engasonumber please use it function.
function validateNonNumericInput(value) {
    // Example validation logic for non-numeric inputs
    if (value.length > 1 && value.length < 5) { // Example: limit the length of the input
        showValidationAlert("Text is too short and vague.");
        return false;
    }
    // Add other non-numeric validations as needed
    return true;
}
		
	//for examination saving we will append a variable for the examination, remark, classification

	
	//exporting data function
//	
//$("#export-pdf-button").on("click", function() {
//  
//    downloadPDF();
//});

		
		function calculateOCM(marks,assessmentData)
		
		{
			
	
			var ocm = 0;
			var assessmentWeight = 0;
			var totalMark = 0;
			console.log("DECEMBER marks", marks.length );
			
			
		
			for (let x = 0; x < assessmentData.length; x++) {
				
				assessmentWeight = parseFloat(assessmentData[x].coursework_weight);
				totalMark = parseFloat(assessmentData[x].total_mark); // Parse the string to a floating-point number
				
				// Calculate the OCM contribution for this row and column
				ocm += (marks[x] / totalMark) * assessmentWeight;
	
			
			console.log("DECEMBER totalMark", totalMark);
			console.log("DECEMBER AssessmentWeight", assessmentData[x].coursework_weight);
			console.log("DECEMBER ocm IS", ocm);
		
		
			}
			
		
			console.log(ocm);
			return ocm;
		}
	
		//####### calculates the  overal Exam Mark with exam mark ###################################

		

		function calculateOverallExamMarkWithOneExam(examMark, examWeight, ocm, assessmentWeight) {
			console.log("DECEMBER calculateOverallExamMarkWithOneExam");
			var ocmContribution = ocm * assessmentWeight * 0.01;
			var examContribution = examMark * examWeight * 0.01;
			var overallExamMark = examContribution + ocmContribution;
			
			console.log("DECEMBER examContribution", examContribution);
			console.log("DECEMBER overallExamMark", overallExamMark);
			
			return parseFloat(overallExamMark).toFixed(2);
		}
		
		function calculateOverallExamMarkWithTwoExams(examMark, examWeight, ocm, assessmentWeight, otherExamMark, otherExamWeight) {
			console.log("DECEMBER calculateOverallExamMarkWithTwoExams");
			var ocmContribution = ocm * assessmentWeight * 0.01;
			var examContribution = examMark * examWeight * 0.01;
			var otherExamContribution = otherExamMark * otherExamWeight * 0.01;
			var overallExamMark = examContribution + ocmContribution + otherExamContribution;
			
			console.log("DECEMBER ocmContribution", ocmContribution);
			console.log("DECEMBER overallExamMark", overallExamMark);
			console.log("DECEMBER examContribution", examContribution);
			console.log("DECEMBER overallExamMark", overallExamMark);

			return parseFloat(overallExamMark).toFixed(2);
		}
		
		
		//####### calculates the  overal Exam Mark with no exam mark ###################################

		


		function calculateOverallExamMarkNoExam(examMark, examWeight, ocm, assessmentWeight) 
		{
			console.log("DECEMBER calculateOverallExamMarkNoExam");
			// Initialize overallExamMark
			var overallExamMark = 0;
		
			// Check if examMark is a valid number and not an empty string
			if (examMark !== "" && !isNaN(examMark)) 
			{
				// Calculate the contribution of the exam to the overall mark
				var examContribution = Number(examMark) * examWeight * 0.01;
				overallExamMark += examContribution;
			}
		
			// Calculate the contribution of the OCM to the overall mark
			var ocmContribution = ocm * assessmentWeight * 0.01;
			overallExamMark += ocmContribution;
		
			// Format the overall exam mark to 2 decimal places
			overallExamMark = parseFloat(overallExamMark).toFixed(2);
		
			return overallExamMark;
		}

//this one calculate the overal courseworkresults for otherExam WithOtherExam and without Exam Mark(the latest)
		function calculateOverallExamMarkWithOtherNoExam(examMark, examWeight, otherExamMark, otherExamWeight, ocm, assessmentWeight) {
			// Initialize overallExamMark
			var overallExamMark = 0;
			console.log("DECEMBER calculateOverallExamMarkWithOtherNoExam");
		
			// Check if examMark is a valid number and not an empty string
			if (examMark !== "" && !isNaN(examMark)) {
				// Calculate the contribution of the exam to the overall mark
				var examContribution = Number(examMark) * examWeight * 0.01;
				overallExamMark += examContribution;
			}
		
			// Check if otherExamMark is a valid number and not an empty string
			if (otherExamMark !== "" && !isNaN(otherExamMark)) {
				// Calculate the contribution of the other exam to the overall mark
				var otherExamContribution = Number(otherExamMark) * otherExamWeight * 0.01;
				overallExamMark += otherExamContribution;
			}
		
			// Calculate the contribution of the OCM to the overall mark
			var ocmContribution = ocm * assessmentWeight * 0.01;
			overallExamMark += ocmContribution;
		
			// Format the overall exam mark to 2 decimal places
			overallExamMark = parseFloat(overallExamMark).toFixed(2);
		
			return overallExamMark;
		}
		
		
		
		
	
	
		function getClassification(mark) 
		{
			
			var mark =parseFloat(mark)
			  console.log(mark);
		
			for (var i = 0; i < markingscheme.length; i++) {
	
				
				if (mark >= markingscheme[i].examResultLowerMark && mark <= markingscheme[i].examResultUpperMark) {
					return markingscheme[i].classification;
				}
			}
		
			//custom alert message for an error.
		}
	
	
		function getRemark(mark) 
		{
			
			var mark =parseFloat(mark)
			  console.log(mark);
		
			for (var i = 0; i < markingscheme.length; i++) {
	
				
				if (mark >= markingscheme[i].examResultLowerMark && mark <= markingscheme[i].examResultUpperMark) {
					return markingscheme[i].remark;
				}
			}
		
			return "N/A";
              	//custom alert mesage
		}
	
	
		
		
		function populateMarksArray(rowData, totalCourseworkItems)
		 {
			var marks = [];
			var startingColumnIndex = 4; 
		
			for (var i = startingColumnIndex; i <= startingColumnIndex + totalCourseworkItems; i++) 
			{
				var value = rowData[i] !== "" ? parseFloat(rowData[i]) : 0; // Convert value to a number or use 0 if empty
				marks.push(value);
			}
			console.log("Total marks:", marks);
	
			return marks;
		}
	
		function stripHTML(str) {
			var temp = document.createElement("div");
			temp.innerHTML = str;
			return temp.textContent || temp.innerText || "";
		}





		// Function to populate the selected or clicked assessment value
$(".marks_table tbody").on(\'click\', \'tr\', function (event) {
    var $table = $(this).closest(".marks_table");
    var row = $(this);
    var columnIndex = row.find(\'td\').index($(event.target));

    // Retrieve the associated column header using the index from the correct table
    var columnHeaderId = $table.find(\'thead th\').eq(columnIndex).attr(\'id\');

    if (columnHeaderId !== selected_assessment_id) {
        selected_assessment_id = columnHeaderId;
        console.log("Selected Assessment ID", columnHeaderId);

        // Search for the assessment data with the matching ID
        var assessment = assessmentData.find(function (item) {
            return item.courseID === columnHeaderId;
        });

        if (assessment) {
            // Populate the elements with data from the selected assessment
            $(\'#coursename\').text("Course Name: " + assessment.CourseDescription);
            $(\'#coursecode\').text("Course Code: " + assessment.coursename);
            $(\'#assessmenttitle\').text("Assessment Title: " + assessment.coursework_title);
            $(\'#assessmenttype\').text("Assessment Type: " + assessment.coursework_type);
            $(\'#assessmentdate\').text("Assessment Date: " + assessment.created_at);
            $(\'#weighting\').text("Marked out of: " + assessment.total_mark);

            $("#assessment-title").text(assessment.coursework_title); // Update the assessment title
            $("#marked-out-of").text(assessment.total_mark); // Update the marked out of
        }

        // Special handling for the "Exam" column
        if (columnHeaderId === "exam") {
            $("#assessment-title").text("Examination Mark"); // Update the text for examination
            $("#marked-out-of").text("100%"); // Update the marked out for examination
        }
    }
});

	

	

			
			
			
				
	



//	validate input	
function validateInput(inputElement) {
    // Wrap the input element with jQuery
    var $input = $(inputElement);

    // Find the closest parent table to ensure the validation is context-specific
    var $table = $input.closest(\'table.marks_table\');

    // Get the index of the td relative to its siblings
    var cellIndex = $input.closest(\'td\').index();

    // Find the corresponding th element within the same table using the index
    var columnName = $table.find(\'thead th\').eq(cellIndex).attr(\'datatype\');
    var totalMark = $table.find(\'thead th\').eq(cellIndex).attr(\'data-total-mark\');

    // Get the value from the input element
    var value = $input.val();

    // Handle non-numeric columns specifically
    if (columnName === "string") {
        return validateNonNumericInput(value);
    } else {
        // Replace comma with dot for decimal numbers and trim white spaces
        value = value.replace(\',\', \'.\').trim();

        // Validate numeric input
        if (isNaN(value)) {
            showValidationAlert("Please enter a valid number.");
            return false;
        }

        var numericValue = parseFloat(value);

        // Check for negative values
        if (numericValue < 0) {
            alert("Marks cannot be negative.");
            return false;
        }

        // Validate against the maximum allowable mark
        if (totalMark && numericValue > parseFloat(totalMark)) {
            showValidationAlert("Marks cannot exceed " + totalMark + ".");
            return false;
        }

        return true;
    }
}



//this function validates the non number column so if anything engasonumber please use it function.
function validateNonNumericInput(value) {
    // Example validation logic for non-numeric inputs
    if (value.length > 1 && value.length < 5) { // Example: limit the length of the input
        showValidationAlert("Text is too short and vague.");
        return false;
    }
    // Add other non-numeric validations as needed
    return true;
}
		
	//for examination saving we will append a variable for the examination, remark, classification

	
	//exporting data function
//	
//$("#export-pdf-button").on("click", function() {
//  
//    downloadPDF();
//});

		
		function calculateOCM(marks,assessmentData)
		
		{
			
	
			var ocm = 0;
			var assessmentWeight = 0;
			var totalMark = 0;
			console.log("DECEMBER marks", marks.length );
			
			
		
			for (let x = 0; x < assessmentData.length; x++) {
				
				assessmentWeight = parseFloat(assessmentData[x].coursework_weight);
				totalMark = parseFloat(assessmentData[x].total_mark); // Parse the string to a floating-point number
				
				// Calculate the OCM contribution for this row and column
				ocm += (marks[x] / totalMark) * assessmentWeight;
	
			
			console.log("DECEMBER totalMark", totalMark);
			console.log("DECEMBER AssessmentWeight", assessmentData[x].coursework_weight);
			console.log("DECEMBER ocm IS", ocm);
		
		
			}
			
		
			console.log(ocm);
			return ocm;
		}
	
		//####### calculates the  overal Exam Mark with exam mark ###################################

		

		function calculateOverallExamMarkWithOneExam(examMark, examWeight, ocm, assessmentWeight) {
			console.log("DECEMBER calculateOverallExamMarkWithOneExam");
			var ocmContribution = ocm * assessmentWeight * 0.01;
			var examContribution = examMark * examWeight * 0.01;
			var overallExamMark = examContribution + ocmContribution;
			
			console.log("DECEMBER examContribution", examContribution);
			console.log("DECEMBER overallExamMark", overallExamMark);
			
			return parseFloat(overallExamMark).toFixed(2);
		}
		
		function calculateOverallExamMarkWithTwoExams(examMark, examWeight, ocm, assessmentWeight, otherExamMark, otherExamWeight) {
			console.log("DECEMBER calculateOverallExamMarkWithTwoExams");
			var ocmContribution = ocm * assessmentWeight * 0.01;
			var examContribution = examMark * examWeight * 0.01;
			var otherExamContribution = otherExamMark * otherExamWeight * 0.01;
			var overallExamMark = examContribution + ocmContribution + otherExamContribution;
			
			console.log("DECEMBER ocmContribution", ocmContribution);
			console.log("DECEMBER overallExamMark", overallExamMark);
			console.log("DECEMBER examContribution", examContribution);
			console.log("DECEMBER overallExamMark", overallExamMark);

			return parseFloat(overallExamMark).toFixed(2);
		}
		
		
		//####### calculates the  overal Exam Mark with no exam mark ###################################

		


		function calculateOverallExamMarkNoExam(examMark, examWeight, ocm, assessmentWeight) 
		{
			console.log("DECEMBER calculateOverallExamMarkNoExam");
			// Initialize overallExamMark
			var overallExamMark = 0;
		
			// Check if examMark is a valid number and not an empty string
			if (examMark !== "" && !isNaN(examMark)) 
			{
				// Calculate the contribution of the exam to the overall mark
				var examContribution = Number(examMark) * examWeight * 0.01;
				overallExamMark += examContribution;
			}
		
			// Calculate the contribution of the OCM to the overall mark
			var ocmContribution = ocm * assessmentWeight * 0.01;
			overallExamMark += ocmContribution;
		
			// Format the overall exam mark to 2 decimal places
			overallExamMark = parseFloat(overallExamMark).toFixed(2);
		
			return overallExamMark;
		}

//this one calculate the overal courseworkresults for otherExam WithOtherExam and without Exam Mark(the latest)
		function calculateOverallExamMarkWithOtherNoExam(examMark, examWeight, otherExamMark, otherExamWeight, ocm, assessmentWeight) {
			// Initialize overallExamMark
			var overallExamMark = 0;
			console.log("DECEMBER calculateOverallExamMarkWithOtherNoExam");
		
			// Check if examMark is a valid number and not an empty string
			if (examMark !== "" && !isNaN(examMark)) {
				// Calculate the contribution of the exam to the overall mark
				var examContribution = Number(examMark) * examWeight * 0.01;
				overallExamMark += examContribution;
			}
		
			// Check if otherExamMark is a valid number and not an empty string
			if (otherExamMark !== "" && !isNaN(otherExamMark)) {
				// Calculate the contribution of the other exam to the overall mark
				var otherExamContribution = Number(otherExamMark) * otherExamWeight * 0.01;
				overallExamMark += otherExamContribution;
			}
		
			// Calculate the contribution of the OCM to the overall mark
			var ocmContribution = ocm * assessmentWeight * 0.01;
			overallExamMark += ocmContribution;
		
			// Format the overall exam mark to 2 decimal places
			overallExamMark = parseFloat(overallExamMark).toFixed(2);
		
			return overallExamMark;
		}
		
		
		
		
	
	
		function getClassification(mark) 
		{
			
			var mark =parseFloat(mark)
			  console.log(mark);
		
			for (var i = 0; i < markingscheme.length; i++) {
	
				
				if (mark >= markingscheme[i].examResultLowerMark && mark <= markingscheme[i].examResultUpperMark) {
					return markingscheme[i].classification;
				}
			}
		
			//custom alert message for an error.
		}
	
	
		function getRemark(mark) 
		{
			
			var mark =parseFloat(mark)
			  console.log(mark);
		
			for (var i = 0; i < markingscheme.length; i++) {
	
				
				if (mark >= markingscheme[i].examResultLowerMark && mark <= markingscheme[i].examResultUpperMark) {
					return markingscheme[i].remark;
				}
			}
		
			return "N/A";
	//custom alert mesage
		}
	
	
		
		
		function populateMarksArray(rowData, totalCourseworkItems)
		 {
			var marks = [];
			var startingColumnIndex = 4; 
		
			for (var i = startingColumnIndex; i <= startingColumnIndex + totalCourseworkItems; i++) 
			{
				var value = rowData[i] !== "" ? parseFloat(rowData[i]) : 0; // Convert value to a number or use 0 if empty
				marks.push(value);
			}
			console.log("Total marks:", marks);
	
			return marks;
		}
	
		function stripHTML(str) {
			var temp = document.createElement("div");
			temp.innerHTML = str;
			return temp.textContent || temp.innerText || "";
		}





		// Function to populate the selected or clicked assessment value
$(".marks_table tbody").on(\'click\', \'tr\', function (event) {
    var $table = $(this).closest(".marks_table");
    var row = $(this);
    var columnIndex = row.find(\'td\').index($(event.target));

    // Retrieve the associated column header using the index from the correct table
    var columnHeaderId = $table.find(\'thead th\').eq(columnIndex).attr(\'id\');

    if (columnHeaderId !== selected_assessment_id) {
        selected_assessment_id = columnHeaderId;
        console.log("Selected Assessment ID", columnHeaderId);

        // Search for the assessment data with the matching ID
        var assessment = assessmentData.find(function (item) {
            return item.courseID === columnHeaderId;
        });

        if (assessment) {
            // Populate the elements with data from the selected assessment
            $(\'#coursename\').text("Course Name: " + assessment.CourseDescription);
            $(\'#coursecode\').text("Course Code: " + assessment.coursename);
            $(\'#assessmenttitle\').text("Assessment Title: " + assessment.coursework_title);
            $(\'#assessmenttype\').text("Assessment Type: " + assessment.coursework_type);
            $(\'#assessmentdate\').text("Assessment Date: " + assessment.created_at);
            $(\'#weighting\').text("Marked out of: " + assessment.total_mark);

            $("#assessment-title").text(assessment.coursework_title); // Update the assessment title
            $("#marked-out-of").text(assessment.total_mark); // Update the marked out of
        }

        // Special handling for the "Exam" column
        if (columnHeaderId === "exam") {
            $("#assessment-title").text("Examination Mark"); // Update the text for examination
            $("#marked-out-of").text("100%"); // Update the marked out for examination
        }
    }
});

	

	

			
			
			
			
				});
				
							
				
	

//new
		//FUNCTION TO ALERT

function showAlert(message) {
	$("#customMessageId").text(message);
	$("#alertContainer").show();
	$(".hiddendiv").show();
	setTimeout(function() {
		$("#alertContainer").fadeOut("slow");
		$(".hiddendiv").fadeOut("slow");
	}, 6000);  // modified to hide after 6 seconds as per your previous message
}

function showValidationAlert(message) {
    // Update the content of the modal
    
	$(\'#validationAlertModal #validationAlertText\').html(\'<span style="color: red;">&nbsp;&nbsp;<i class="fa fa-times"></i>&nbsp;&nbsp;&nbsp;&nbsp;\' + message + \'</span>\');


    // Show the modal
    $(\'#validationAlertModal\').modal(\'show\');
}


function showInfoAlert(message, callback) {
	return new Promise((resolve, reject) => {
    $(\'#InformationAlertModal #validationAlertText\').html(\'<span style="color: blue;">\' + message + \'</span>\');

    // Show the modal
    $(\'#InformationAlertModal\').modal(\'show\');
	// Bind the click event to the confirm button
	$(\'#confirmButton\').on(\'click\', function() {
		confValue = true;
		// alert(confValue);
		$(\'#InformationAlertModal\').modal(\'hide\');
		resolve(); // Resolve the promise when the button is clicked
	});
});
}


//function to handle the conf modal ////
//set the confirm button to true 

    $(\'#confirmButton\').on(\'click\', function() {
        confValue = true;
		// alert(confValue);
       
        $(\'#InformationAlertModal\').modal(\'hide\');
   
	});
	
// //set the confirm button to false

$(\'#confirmCancel\').on(\'click\', function() {
	confValue = false;
	// alert(confValue);
	$(\'#InformationAlertModal\').modal(\'hide\');
  return;
});



    //download for the carry
    	
  $(\'.export-carry-pdf-button\').on(\'click\', function() {
  
    const doc = new window.jspdf.jsPDF();
    console.log(\'jsPDF instance created\');

    // University Header Section
    doc.setFont(\'sans-serif\'); 
    doc.setFontSize(10); 
    doc.setTextColor(0, 102, 204); 
    doc.text(\'NATIONAL UNIVERSITY OF SCIENCE AND TECHNOLOGY\', 20, 20);

    // Smaller font size for contact details, placed directly below the university name
    doc.setFontSize(6);
    let contactDetailsY = 25; 
    doc.setTextColor(135, 206, 235); 
    doc.text(\'Telephones: +263-292-282842, Ext: 2362 or 2392, Fax: +263-292-286803\', 20, contactDetailsY);


    doc.setLineHeightFactor(0.6);
    contactDetailsY += 4; 
    doc.text(\'Email: icts@nust.ac.zw\', 20, contactDetailsY);
    contactDetailsY += 4; 
    doc.text(\'Facebook: @NUST.ZIM\', 20, contactDetailsY);
    contactDetailsY += 4; 
    doc.text(\'Twitter: @nustzim\', 20, contactDetailsY);
    

    // Program Details
    doc.setFont(\'sans-serif\', \'bold\');
    doc.setFontSize(6);
    doc.text(\'Programme Name:\', 20, 50);
    doc.setFont(\'sans-serif\', \'normal\');

       doc.text(\'' . $class . '(carry)\', 70, 50);

    // Table generation code
    var table = document.getElementById("carry_students_table");
    var allRows = table.getElementsByTagName("tr");
    var pdfTable = []; // Declaration of pdfTable

    // Extract text from table rows and columns
    for (var i = 0; i < allRows.length; i++) {
        var row = [], cols = allRows[i].querySelectorAll("td, th");
        for (var j = 0; j < cols.length; j++) {
            row.push(cols[j].innerText);
        }
        pdfTable.push(row);
    }

    // Check if pdfTable has data before using it
    if (pdfTable.length > 0) {
        // Now use pdfTable for autoTable method
        doc.autoTable({
            startY: 80,
            head: [pdfTable[0]],
            body: pdfTable.slice(1),
            styles: { fontSize: 6, cellPadding: 1 }, // Adjusted styles
            headStyles: { fillColor: [220, 220, 220], textColor: [0, 0, 0]}, // Adjusted head styles
            theme: \'grid\'
        });
    } else {
        console.error(\'pdfTable is empty or not defined\');
    }

    // for footer, page numbers.
     const pageCount = doc.internal.getNumberOfPages();
    for (let i = 1; i <= pageCount; i++) {
        doc.setPage(i);
        doc.setFont(\'sans-serif\', \'italic\');
        doc.setFontSize(4);
        doc.setTextColor(135, 206, 250);
        doc.text(\'Powered by ICTS NUST\', 105, 285, null, null, \'center\');
        doc.text(\'Page \' + String(i) + \' of \' + String(pageCount), 200, 285);
    }

    // Adding page border
    doc.setDrawColor(0);
    doc.setLineWidth(0.000001);
    doc.rect(10, 10, 190, 277);
    doc.save(\'(' . $coursecode . ')  ' . $prog_short_code . ' P' . $yearofstudy . ' S' . $semester . ' 2023(carry)\');
    console.log(\'PDF saved\');
    alert("Done exporting, please save on Computer...");
});

    
    
    

		
 </script>';


// include $this->core->conf['conf']['formPath'] . "examcapture.form.php";


	}

	// END OF THE CURLY BRACKETS 







  
//

public function logsExaminationtest()
{
    $db = $this->core->database;

    $courselecture_id = trim($this->core->cleanGet['courselecture_id'] ?? '');

    if (empty($courselecture_id)) {
        echo '<script>alert("Error: No Class ID provided."); window.close();</script>';
        return;
    }

    // --- SQL LOGIC ---
    // Groups by COALESCE(col, 0) to ensure NULL and 0 are treated as the same unique record
    $sql = "SELECT 
                MAX(created_at) as created_at, 
                user_id, 
                student_id, 
                course_id,
                coursework_id, 
                action_type, 
                target_table,
                COALESCE(cw_mark, 0) as cw_mark,
                COALESCE(other_exam, 0) as other_exam,
                COALESCE(exam_mark, 0) as exam_mark
            FROM assessmentresults_audit_logs 
            WHERE courselecture_id = '" . $db->escape($courselecture_id) . "'
            GROUP BY 
                user_id, 
                student_id, 
                course_id, 
                courselecture_id, 
                coursework_id, 
                action_type, 
                target_table, 
                grade, 
                COALESCE(cw_mark, 0), 
                COALESCE(other_exam, 0), 
                COALESCE(exam_mark, 0), 
                COALESCE(overall_mark, 0)
            ORDER BY created_at ASC 
            LIMIT 2500";

    $result = $db->doSelectQuery($sql);

    $tableRowsHtml = '';
    $rowCount = 0;
    $courseCodeDisplay = "N/A";

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $rowCount++;

            if ($courseCodeDisplay === "N/A" && !empty($row['course_id'])) {
                $courseCodeDisplay = $row['course_id'];
            }

            $actionLabel = 'Unknown';
            if ($row['action_type'] === 'INSERT') {
                $actionLabel = 'New Entry';
            } elseif ($row['action_type'] === 'UPDATE') {
                $actionLabel = 'Modification';
            } elseif ($row['action_type'] === 'DELETE') {
                $actionLabel = 'Removal';
            }

            $targetLabel = $row['target_table'];
            if ($row['target_table'] === 'courseworkresults') {
                $targetLabel = 'Continuous Assessment';
            } elseif ($row['target_table'] === 'courseresultssummary') {
                $targetLabel = 'Final Exam / Summary';
            }

           
            
            $contextParts = [];
            if (!empty($row['student_id'])) {
                $contextParts[] = "<strong>Std:</strong> " . htmlspecialchars($row['student_id']);
            }
            if (!empty($row['coursework_id'])) {
                $contextParts[] = "<strong>Assig:</strong> " . htmlspecialchars($row['coursework_id']);
            } else {
                $contextParts[] = "General Exam";
            }
            $contextStr = implode('<br>', $contextParts);

           
            
            $cwDisplay    = ($row['cw_mark'] == 0) ? '' : htmlspecialchars($row['cw_mark']);
            $otherDisplay = ($row['other_exam'] == 0) ? '' : htmlspecialchars($row['other_exam']);
            $examDisplay  = ($row['exam_mark'] == 0) ? '' : htmlspecialchars($row['exam_mark']);

          
            
            $dateStr = date('d M Y, H:i', strtotime($row['created_at']));

           
            
            $tableRowsHtml .= '
            <tr>
                <td class="c" style="width:4%;">' . $rowCount . '</td>
                <td style="width:13%;">' . $dateStr . '</td>
                <td style="width:12%;">' . htmlspecialchars($row['user_id']) . '</td>
                <td style="width:23%;">' . $contextStr . '</td>
                <td class="c" style="width:7%;">' . $cwDisplay . '</td>
                <td class="c" style="width:7%;">' . $otherDisplay . '</td>
                <td class="c" style="width:7%;">' . $examDisplay . '</td>
                <td style="width:12%;">' . $actionLabel . '</td>
                <td style="width:15%;">' . $targetLabel . '</td>
            </tr>';
        }
    } else {
        $tableRowsHtml = '<tr><td colspan="9" style="text-align:center; padding:20px;">No audit records found.</td></tr>';
    }

 
    
    $logoPath = 'templates/mobile/images/header.png';

    
    $robotoReg  = $this->core->conf['conf']['path'] . '/fonts/Roboto-Regular.ttf';
    $robotoBold = $this->core->conf['conf']['path'] . '/fonts/Roboto-Bold.ttf';

    $today = date('j F Y, H:i');


    
    $courseLabel = "Course Code: " . $courseCodeDisplay;
    $progLabel   = "Class ID: " . $courselecture_id;
    $compactMeta = "Total Records: " . $rowCount;

    $html = '
    <html><head><meta charset="utf-8">
    <style>
        @font-face { font-family: "Roboto"; src: url("' . $robotoReg . '") format("truetype"); font-weight: 400; }
        @font-face { font-family: "Roboto"; src: url("' . $robotoBold . '") format("truetype"); font-weight: 700; }

        @page { margin: 150px 40px 110px 40px; }

        body   { font-family: "Roboto", sans-serif; font-size: 9px; color:#000; }

        header { position: fixed; top: -120px; left: 0; right: 0; height: 110px; }
        footer { position: fixed; bottom: -95px; left:0; right:0; height:95px; }

        .nust-header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 1px solid #bbb; /* muted line */
            padding-bottom: 5px;
            margin-bottom: 10px;
        }
        .nust-logo-cell { width: 15%; vertical-align: top; padding-right: 10px; }
        .nust-content-cell { width: 85%; vertical-align: top; }
        .nust-title {
            font-family: "Times New Roman", serif;
            color: #000;
            font-size: 18px;
            font-weight: bold;
            text-align: left;
            margin-bottom: 8px;
            text-transform: uppercase;
        }
        .nust-details-table {
            width: 100%;
            border-collapse: collapse;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
            color: #000;
            line-height: 1.3;
        }

        .h1  { font-size:14px; font-weight:700; margin:8px 0; text-align:center; text-transform:uppercase; }
        .course-line { font-size:9px; margin-bottom:2px; text-align:center; }
        .prog-line   { font-size:8.5px;  color:#222; margin-bottom:2px; text-align:center; }
        .meta-compact{ font-size:8.5px;  color:#222; margin-bottom:8px; text-align:center; }

        .footer-wrap { width:100%; border-top:1px solid #ddd; padding-top:6px; font-size:7.5px; color:#444; text-align:center; }

        /* ====== BORDERLESS / NO COLOR / SPACED TABLE (like your screenshot) ====== */
        table.results{
            width:100%;
            border-collapse: separate;         /* allow spacing between rows */
            border-spacing: 0 6px;             /* row gap */
            margin-top: 6px;
            font-family: "Roboto", sans-serif;
        }

        table.results thead th{
            background: transparent;           /* no grey */
            color:#000;
            font-size: 8px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .3px;
            text-align: left;
            padding: 2px 4px 6px 4px;
            border: none;
            border-bottom: 1px solid #cfcfcf;  /* muted line under header */
            vertical-align: middle;
            white-space: nowrap;
        }

        table.results tbody td{
            font-size: 8.5px;
            padding: 2px 4px;                  /* airy but clean */
            border: none;                      /* borderless rows */
            vertical-align: top;
        }

        .c{ text-align:center; }
    </style>
    </head>
    <body>
    <header>
        <table class="nust-header-table">
            <tr>
                <td class="nust-logo-cell">
                    <img src="' . $logoPath . '" alt="Logo" style="width: 85px; height: auto;">
                </td>
                <td class="nust-content-cell">
                    <div class="nust-title">National University of Science and Technology</div>

                    <table class="nust-details-table">
                        <tr>
                            <td style="width: 50%; vertical-align: top;">
                                Cnr Gwanda Road/Cecil Avenue,<br>
                                P.O. Box AC 939<br>
                                Ascot, Bulawayo, Zimbabwe<br>
                                <a href="http://www.nust.ac.zw" style="text-decoration:none;color:#000;">www.nust.ac.zw</a>
                            </td>
                            <td style="width: 50%; vertical-align: top; text-align:left; padding-left:20px;">
                                <strong>Telephones:</strong> +263-292-282842<br/>
                                <strong>Ext:</strong> 2362 or 2392<br/>
                                <strong>Fax:</strong> +263-292-286803<br/>
                                <strong>Email:</strong> admissions@nust.ac.zw<br/>
                                <strong>Facebook:</strong> @NUST.ZIM <strong>Twitter:</strong> @nustzim
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </header>

    <footer>
        <div class="footer-wrap">
            Generated on ' . $today . '
        </div>
    </footer>

    <main>
        <h1 class="h1">ASSESSMENT CHANGE LOG REPORT</h1>
        <div class="course-line">' . htmlspecialchars($courseLabel, ENT_QUOTES, "UTF-8") . '</div>
        <div class="prog-line">' . htmlspecialchars($progLabel, ENT_QUOTES, "UTF-8") . '</div>
        <div class="meta-compact">' . htmlspecialchars($compactMeta, ENT_QUOTES, "UTF-8") . '</div>

        <table class="results">
            <thead>
                <tr>
                    <th style="width:4%; text-align:center;">No.</th>
                    <th style="width:13%;">Date & Time</th>
                    <th style="width:12%;">User</th>
                    <th style="width:23%;">Affected Record</th>
                    <th style="width:7%; text-align:center;">OCW</th>
                    <th style="width:7%; text-align:center;">Other</th>
                    <th style="width:7%; text-align:center;">Exam</th>
                    <th style="width:12%;">Type</th>
                    <th style="width:15%;">Area</th>
                </tr>
            </thead>
            <tbody>' .
                $tableRowsHtml . '
            </tbody>
        </table>
    </main>
    </body></html>';

    require_once __DIR__ . '/../../vendor/autoload.php';
    $dompdf = new \Dompdf\Dompdf();
    $dompdf->getOptions()->setChroot("/var/www/html/");

    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    $dir = "/var/www/html/datastore/output/logs/";
    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }

    $filename = 'AuditLog_' . $courselecture_id . '_' . date('Ymd_His') . '.pdf';
    file_put_contents($dir . $filename, $dompdf->output());

    $base = rtrim($this->core->conf['conf']['path'] ?? '', '/');
    $url  = $base . '/datastore/output/logs/' . rawurlencode($filename);

    echo '<script>window.location.href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '";</script>';
    echo '<noscript><meta http-equiv="refresh" content="0; url=' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '"></noscript>';
}






 public function exportExaminationtest($selects)
{
    //  echo 'here'; die();
    $db = $this->core->database;

    
    $selectedclass       = trim($this->core->cleanGet['selectedclass']    ?? ''); // courselecture_id
    $selectedcampus      = trim($this->core->cleanGet['selectedcampus']   ?? '');
    $selectedformat      = trim($this->core->cleanGet['selectedformat']   ?? '');
    $selectedyearofstudy = trim($this->core->cleanGet['yearofstudy']      ?? '');
    $selectedsemester    = trim($this->core->cleanGet['semester']         ?? '');
    $academicyear        = trim($this->core->cleanGet['year']             ?? '');
    $selectedprogID      = trim($this->core->cleanGet['progID']           ?? '');
    $progShortName       = trim($this->core->cleanGet['shortName']        ?? '');
    $programmeName       = trim($this->core->cleanGet['programme']        ?? '');
    $periodID            = trim($this->core->cleanGet['periodID']         ?? '');

    // Helper for filename-safe labels
    $slug = function ($s) {
        $s = preg_replace('/[^A-Za-z0-9\-_.]+/', '-', (string) $s);
        return trim($s, '-');
    };

    // If class missing, tiny info PDF
    if ($selectedclass === '') {
        $today = date('j F, Y');
        $html  = '
        <html><head><meta charset="utf-8">
        <style>
            @page { margin: 70px 40px 70px 40px; }
            body { font-family: Arial, sans-serif; font-size: 11px; }
            header { position: fixed; top: -50px; left:0; right:0; height: 40px; text-align:center; }
            .h1 { font-size: 16px; font-weight: bold; }
            .msg { margin-top: 20px; }
        </style>
        </head>
        <body>
        <header>
            <div class="h1">Class Results Report</div>
        </header>
        <main>
            <div class="msg">A valid class (<code>selectedclass</code>) is required.</div>
            <div class="msg" style="color:#555;">Generated on ' . $today . '</div>
        </main>
        </body></html>';

        require_once __DIR__ . '/../../vendor/autoload.php';
        $dompdf = new \Dompdf\Dompdf();
        $dompdf->getOptions()->setChroot("/var/www/html/");
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $dir = "/var/www/html/datastore/output/results/";
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
        $filename = 'RESULTS_missing-class_' . date('Ymd-His') . '.pdf';
        file_put_contents($dir . $filename, $dompdf->output());

        $base = rtrim($this->core->conf['conf']['path'] ?? '', '/');
        $url  = $base . '/datastore/output/results/' . rawurlencode($filename);

        echo '<script>window.location.href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '";</script>';
        echo '<noscript><meta http-equiv="refresh" content="0; url=' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '"></noscript>';
        return;
    }

   
    
    $selectedclassEsc       = $db->escape($selectedclass);
    $selectedcampusEsc      = $db->escape($selectedcampus);
    $selectedformatEsc      = $db->escape($selectedformat);
    $selectedyearofstudyEsc = $db->escape($selectedyearofstudy);
    $selectedsemesterEsc    = $db->escape($selectedsemester);
    $selectedprogIDEsc      = $db->escape($selectedprogID);
    $periodIDEsc            = $db->escape($periodID);

   
    
    $metaSql = "
        SELECT
            cl.ID            AS courselecture_id,
            cl.year          AS academicyear,
            cl.semester,
            cl.lecturerECno  AS lecturer_id,
            c.Name           AS course_code,
            c.CourseDescription AS course_name,
            s.ShortName      AS programme_short,
            s.Name           AS programme_name
        FROM edurole.courselecture cl
        INNER JOIN edurole.courses c ON c.ID = cl.coursecode
        INNER JOIN edurole.study   s ON s.ID = cl.classcode
        WHERE cl.ID = '{$selectedclassEsc}'
        LIMIT 1
    ";

    $metaRes = $db->doSelectQuery($metaSql);

    $meta = [
        'course_code'     => '',
        'course_name'     => '',
        'programme_short' => $progShortName,
        'programme_name'  => $programmeName,
        'academicyear'    => $academicyear,
        'semester'        => $selectedsemester,
        'yearofstudy'     => $selectedyearofstudy,
        'lecturer_id'     => '',            // will be shown as "Lecturer"
        'campus'          => $selectedcampus,
        'format'          => $selectedformat,
    ];

    if ($metaRes && $metaRes->num_rows) {
        $m                       = $metaRes->fetch_assoc();
        $meta['course_code']     = $m['course_code'] ?? $meta['course_code'];
        $meta['course_name']     = $m['course_name'] ?? $meta['course_name'];
        $meta['programme_short'] = $m['programme_short'] ?? $meta['programme_short'];
        $meta['programme_name']  = $m['programme_name'] ?? $meta['programme_name'];
        $meta['academicyear']    = $m['academicyear'] ?? $meta['academicyear'];
        $meta['semester']        = $m['semester'] ?? $meta['semester'];
        $meta['lecturer_id']     = $m['lecturer_id'] ?? $meta['lecturer_id'];
    }

   
    
    $resultsSql = "
        SELECT
            bi.ID         AS student_id,
            bi.FirstName  AS first_name,
            bi.Surname    AS surname,
            crs.courseWorkMark,
            crs.otherExam,
            crs.finalExaminationMark,
            crs.overallMark,
            crs.resultGrade,
            crs.courseRemark,
            crs.comment,
            crs.board_reviewed,
            crs.created_at  AS captured_at  -- if you have these columns
            -- , crs.captured_by  AS captured_by  -- uncomment + add above
        FROM student_progression sp
        INNER JOIN study s
            ON s.ShortName = sp.programme_code
           AND s.ProgrammesAvailable = 1
           AND s.ID = '{$selectedprogIDEsc}'      
        INNER JOIN courselecture cl
            ON cl.ID = '{$selectedclassEsc}'
            AND  cl.classcode = s.ID            
        INNER JOIN `basic-information` bi
            ON bi.ID = sp.student_id
        LEFT JOIN `student-study-link` `ssl`
            ON `ssl`.StudentID = sp.student_id
        LEFT JOIN edurole.courseresultssummary crs
            ON crs.student_id       = sp.student_id
           AND crs.courselecture_id = cl.ID
        WHERE
            sp.exam_centre = '{$selectedcampusEsc}'
            AND sp.format  = '{$selectedformatEsc}'
            AND sp.periodID = '{$periodIDEsc}'
            AND sp.part    = '{$selectedyearofstudyEsc}'
            AND sp.semester= '{$selectedsemesterEsc}'
    ";

    // ---------- REGULATION FILTER (same logic as screen) ----------
    if (isset($regulationCode, $regYear) && $regulationCode !== '' && (int) $regYear > 0) {
        $regYearInt = (int) $regYear;
        if ($regYearInt >= 2023) {
            $resultsSql .= "
            AND (ssl.regulation_code IS NULL
                 OR RIGHT(ssl.regulation_code, 4) >= '{$regYearInt}')";
        } else {
            $resultsSql .= "
            AND RIGHT(ssl.regulation_code, 4) = '{$regYearInt}'";
        }
    }

    // Finally add ORDER BY
    $resultsSql .= "
       
        ORDER BY bi.Surname, bi.FirstName
    ";

    $res = $db->doSelectQuery($resultsSql);

    $rows = [];
    if ($res && $res->num_rows) {
        $i = 0;
        while ($r = $res->fetch_assoc()) {
            $i++;

            $cw      = $r['courseWorkMark'];
            $other   = $r['otherExam'];
            $exam    = $r['finalExaminationMark'];
            $overall = $r['overallMark'];

            $hasCw      = ($cw !== null && $cw !== '');
            $hasExam    = ($exam !== null && $exam !== '');
            $hasOverall = ($overall !== null && $overall !== '');

            // ------------------------------
            // Posted? (compact code) + Post Type (text)
            // ------------------------------
            $postedCode = 'NP';          // Not Posted
            $postType   = 'No Marks';

            if ($hasCw && !$hasExam) {
                // coursework only  (like -2)
                $postedCode = 'CW';
                $postType   = 'Coursework Only';
            } elseif ($hasCw && $hasExam) {
                // both posted (like 0)
                $postedCode = 'CX';
                $postType   = 'Coursework & Exam';
            } elseif ($hasExam && !$hasCw) {
                $postedCode = 'EX';
                $postType   = 'Exam Only';
            }

           
            
            $boardStage = 'NS'; // Not Submitted
            $br         = $r['board_reviewed'];

            if ($br === null || $br === '' || (int) $br === -1) {
                $boardStage = '--';   // Not Submitted
            } elseif ((int) $br === -2) {
                $boardStage = 'LEC';  // Lecturer Submitted
            } elseif ((int) $br === 0) {
                $boardStage = 'Dept.';   // Department Board
            } elseif ((int) $br === 1 || (int) $br === 2) {
                $boardStage = 'Fac.';   // Faculty Board
            } elseif ((int) $br === 3) {
                $boardStage = 'Aca.';   // Academic Board
            } else {
                $boardStage = 'Published.';  // Published / Final
            }


            
            $capturedAtRaw = $r['captured_at'] ?? '';
            $capturedAt    = '';
            if (!empty($capturedAtRaw)) {
                $ts = strtotime($capturedAtRaw);
                $capturedAt = $ts ? date('Y-m-d', $ts) : $capturedAtRaw;
            }
            $capturedBy = $r['captured_by'] ?? '';

            $nameRaw = trim(($r['first_name'] ?? '') . ' ' . ($r['surname'] ?? ''));
            $name    = strtoupper($nameRaw); 
            $rows[] = [
                'no'          => $i,
                'student_id'  => $r['student_id'],
                'name'        => $name,
                'cw'          => $cw,
                'otherExam'   => $other,
                'exam'        => $exam,
                'overall'     => $overall,
                'grade'       => $r['resultGrade'],
                'remark'      => $r['courseRemark'],
                'comment'     => $r['comment'],
                'postedFlag'  => $postedCode,
                'postType'    => $postType,
                'boardStage'  => $boardStage,
                'captured_at' => $capturedAt,
                'captured_by' => $capturedBy,
            ];
        }
    }

    


    if (!$rows) {
        $today = date('j F, Y');

        $logoPath   = 'templates/mobile/images/header.png';
        $qrPath     = 'templates/mobile/images/verifyaward_qr.png'; 
        $robotoReg  = 'assets/fonts/roboto/Roboto-Regular.ttf';
        $robotoBold = 'assets/fonts/roboto/Roboto-Bold.ttf';

        $html = '
        <html><head><meta charset="utf-8">
        <style>
        @font-face { font-family: "Roboto"; src: url("' . $robotoReg . '") format("truetype"); font-weight: 400; }
        @font-face { font-family: "Roboto"; src: url("' . $robotoBold . '") format("truetype"); font-weight: 700; }

        /* Crucial: Reserve space at the top of the page for the fixed header */
        @page { margin: 150px 40px 110px 40px; }

        body   { font-family: "Roboto", sans-serif; font-size: 11px; color:#000; }

        /* Fixed Header Position */
        header { position: fixed; top: -120px; left: 0; right: 0; height: 110px; }
        footer { position: fixed; bottom: -95px; left:0; right:0; height:95px; }

        /* Main Container Table with Bottom Border */
        .nust-header-table { 
            width: 100%; 
            border-collapse: collapse; 
            border-bottom: 1px solid #999; /* lighter line under the header */
            padding-bottom: 5px; 
            margin-bottom: 10px; 
        }

        /* Logo Column */
        .nust-logo-cell { 
            width: 15%; 
            vertical-align: top; 
            padding-right: 10px; 
        }

        /* Content Column (Title + Address) */
        .nust-content-cell { 
            width: 85%; 
            vertical-align: top; 
        }

        /* University Title Style (Blue, Serif, Uppercase) */
        .nust-title { 
            font-family: "Times New Roman", serif; 
            color: #003366; 
            font-size: 18px; 
            font-weight: bold; 
            text-align: left; 
            margin-bottom: 8px; 
            text-transform: uppercase; 
        }

        /* Address & Contact Info Table */
        .nust-details-table { 
            width: 100%; 
            border-collapse: collapse; 
            font-family: Arial, Helvetica, sans-serif; 
            font-size: 10px; 
            color: #000; 
            line-height: 1.3; 
        }

        .h1  { font-size:16px; font-weight:700; margin:8px 0; text-align:center; }
        .msg { margin-top: 16px; text-align:center; }
        .footer-wrap { width:100%; border-top:1px solid #ddd; padding-top:6px; font-size:7px; color:#444; }
        </style>
        </head>
        <body>
        <header>
            <table class="nust-header-table">
                <tr>
                    <!-- Left: Logo -->
                    <td class="nust-logo-cell">
                        <img src="' . $logoPath . '" alt="Logo" style="width: 85px; height: auto;">
                    </td>
                    
                    <!-- Right: Text Content -->
                    <td class="nust-content-cell">
                        <!-- 1. University Name -->
                        <div class="nust-title">National University of Science and Technology</div>
                        
                        <!-- 2. Two-Column Address & Contact Info -->
                        <table class="nust-details-table">
                            <tr>
                                <!-- Address -->
                                <td style="width: 50%; vertical-align: top;">
                                    Cnr Gwanda Road/Cecil Avenue,<br>
                                    P.O. Box AC 939<br>
                                    Ascot, Bulawayo, Zimbabwe<br>
                                    <a href="http://www.nust.ac.zw" style="text-decoration:none;color:#000;">www.nust.ac.zw</a>
                                </td>
                                
                                <!-- Contact Details -->
                                <td style="width: 50%; vertical-align: top; text-align:left; padding-left:20px;">
                                    <strong>Telephones:</strong> +263-292-282842<br/>
                                    <strong>Ext:</strong> 2362 or 2392<br/>
                                    <strong>Fax:</strong> +263-292-286803<br/>
                                    <strong>Email:</strong> admissions@nust.ac.zw<br/>
                                    <strong>Facebook:</strong> @NUST.ZIM <strong>Twitter:</strong> @nustzim
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </header>

        <footer>
            <div class="footer-wrap">
                Generated on ' . $today . '
            </div>
        </footer>

        <main>
            <h1 class="h1">CLASS RESULTS REPORT</h1>
            <div class="msg">No results found for the selected class.</div>
        </main>
        </body></html>';

        require_once __DIR__ . '/../../vendor/autoload.php';
        $dompdf = new \Dompdf\Dompdf();
        $opts   = $dompdf->getOptions();
        $opts->setChroot("/var/www/html/");
        $opts->set('isRemoteEnabled', true);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $dir = "/var/www/html/datastore/output/results/";
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
        $filename = 'RESULTS_' . $slug($meta['course_code']) . '_empty_' . date('Ymd-His') . '.pdf';
        file_put_contents($dir . $filename, $dompdf->output());

        $base = rtrim($this->core->conf['conf']['path'] ?? '', '/');
        $url  = $base . '/datastore/output/results/' . rawurlencode($filename);

        echo '<script>window.location.href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '";</script>';
        echo '<noscript><meta http-equiv="refresh" content="0; url=' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '"></noscript>';
        return;
    }

    


    $hasOtherExam = false;
    foreach ($rows as $r) {
        if ($r['otherExam'] !== null && $r['otherExam'] !== '') {
            $hasOtherExam = true;
            break;
        }
    }


    $headerMarksCols = '
                    <th style="width:42px;text-align:center;">OCW</th>';
    if ($hasOtherExam) {
        $headerMarksCols .= '
                    <th style="width:42px;text-align:center;">OTHER EXAM</th>';
    }
    $headerMarksCols .= '
                    <th style="width:42px;text-align:center;">EXAM</th>
                    <th style="width:42px;text-align:center;">OM%</th>
                    <th style="width:35px;text-align:center;">GRADE</th>
                    <th style="width:70px;text-align:center;">REMARK</th>
                    <th style="text-align:left;">COMMENT</th>
                    <th style="width:38px;text-align:center;">POSTED?</th>
                    <th style="width:70px;text-align:center;">BOARD STAGE</th>
                    <th style="width:70px;text-align:left;">CAPTURED AT</th>';

  
    $tableRowsHtml = '';
    foreach ($rows as $row) {
        $tableRowsHtml .= '<tr>';
        $tableRowsHtml .= '<td>' . htmlspecialchars($row['no'], ENT_QUOTES, 'UTF-8') . '</td>';
        $tableRowsHtml .= '<td>' . htmlspecialchars($row['student_id'], ENT_QUOTES, 'UTF-8') . '</td>';
        $tableRowsHtml .= '<td>' . htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') . '</td>';

        $tableRowsHtml .= '<td style="text-align:center;">' . htmlspecialchars($row['cw'], ENT_QUOTES, 'UTF-8') . '</td>';

        if ($hasOtherExam) {
            $tableRowsHtml .= '<td style="text-align:center;">' . htmlspecialchars($row['otherExam'], ENT_QUOTES, 'UTF-8') . '</td>';
        }

        $tableRowsHtml .= '<td style="text-align:center;">' . htmlspecialchars($row['exam'], ENT_QUOTES, 'UTF-8') . '</td>';
        $tableRowsHtml .= '<td style="text-align:center;">' . htmlspecialchars($row['overall'], ENT_QUOTES, 'UTF-8') . '</td>';
        $tableRowsHtml .= '<td style="text-align:center;">' . htmlspecialchars($row['grade'], ENT_QUOTES, 'UTF-8') . '</td>';
        $tableRowsHtml .= '<td style="text-align:center;">' . htmlspecialchars($row['remark'], ENT_QUOTES, 'UTF-8') . '</td>';
        $tableRowsHtml .= '<td>' . htmlspecialchars($row['comment'], ENT_QUOTES, 'UTF-8') . '</td>';

        $tableRowsHtml .= '<td style="text-align:center;">' . htmlspecialchars($row['postedFlag'], ENT_QUOTES, 'UTF-8') . '</td>';
        $tableRowsHtml .= '<td style="text-align:center;">' . htmlspecialchars($row['boardStage'], ENT_QUOTES, 'UTF-8') . '</td>';
        $tableRowsHtml .= '<td>' . htmlspecialchars($row['captured_at'], ENT_QUOTES, 'UTF-8') . '</td>';
        $tableRowsHtml .= '</tr>';
    }




    
    $title = "CLASS RESULTS REPORT";

   
    $courseLabel = 'Course: ' . $meta['course_name'] . ' (' . $meta['course_code'] . ')';

   
    $progLabel   = $meta['programme_name'] . '(' . $meta['programme_short'] . ')';

    $compactMeta = 'Part: ' . $meta['yearofstudy']
        . ' | Semester: ' . $meta['semester']
        . ' | ID: ' . $selectedclass
        . ' | Academic Year: ' . $meta['academicyear']
        . ' | Campus: ' . $meta['campus']
        . ' | Format: ' . $meta['format']
        . ' | Lecturer (Captured): ' . $meta['lecturer_id'];

    $today = date('j F, Y');

    $logoPath   = 'templates/mobile/images/header.png';
    $qrPath     = 'templates/mobile/images/verifyaward_qr.png';
    $robotoReg  = 'assets/fonts/roboto/Roboto-Regular.ttf';
    $robotoBold = 'assets/fonts/roboto/Roboto-Bold.ttf';

    $html = '
    <html><head><meta charset="utf-8">
    <style>
    @font-face { font-family: "Roboto"; src: url("' . $robotoReg . '") format("truetype"); font-weight: 400; }
    @font-face { font-family: "Roboto"; src: url("' . $robotoBold . '") format("truetype"); font-weight: 700; }

    /* Crucial: Reserve space at the top of the page for the fixed header */
    @page { margin: 150px 40px 110px 40px; }

    body   { font-family: "Roboto", sans-serif; font-size: 9px; color:#000; }

    /* Fixed Header Position */
    header { position: fixed; top: -120px; left: 0; right: 0; height: 110px; }
    footer { position: fixed; bottom: -95px; left:0; right:0; height:95px; }

    /* Main Container Table with Bottom Border */
    .nust-header-table { 
        width: 100%; 
        border-collapse: collapse; 
        border-bottom: 1px solid #999; /* lighter line under the header */
        padding-bottom: 5px; 
        margin-bottom: 10px; 
    }

    /* Logo Column */
    .nust-logo-cell { 
        width: 15%; 
        vertical-align: top; 
        padding-right: 10px; 
    }

    /* Content Column (Title + Address) */
    .nust-content-cell { 
        width: 85%; 
        vertical-align: top; 
    }

    /* University Title Style (Blue, Serif, Uppercase) */
    .nust-title { 
        font-family: "Times New Roman", serif; 
        color: #003366; 
        font-size: 18px; 
        font-weight: bold; 
        text-align: left; 
        margin-bottom: 8px; 
        text-transform: uppercase; 
    }

    /* Address & Contact Info Table */
    .nust-details-table { 
        width: 100%; 
        border-collapse: collapse; 
        font-family: Arial, Helvetica, sans-serif; 
        font-size: 10px; 
        color: #000; 
        line-height: 1.3; 
    }

    .main-title       { font-size:13px; font-weight:700; margin:2px 0 4px 0; text-transform:uppercase; text-align:center; }
    .course-line      { font-size:8.5px; margin-bottom:2px; text-align:center; }
    .prog-line        { font-size:8px;  color:#444; margin-bottom:2px; text-align:center; }
    .meta-compact     { font-size:7.8px;  color:#444; margin-bottom:4px; text-align:center; }

    table.results { width:100%; border-collapse:collapse; margin-top:4px; font-family:"Roboto",sans-serif; }
    table.results th,
    table.results td { border:none; padding:4px 3px; } /* more vertical padding for visibility */

    table.results th {
        background:#ffffff;
        font-size:7.5px;
        font-weight:700;
        text-align:left;
    }
    table.results td {
        font-size:7px;
    }

    .legend-block {
        margin-top:6px;
        padding-top:3px;
        border-top:0.3px solid #999;
        font-size:7px;
        color:#444;
        page-break-inside:avoid;
    }

    .footer-wrap { width:100%; border-top:1px solid #ddd; padding-top:6px; font-size:7px; color:#444; }
    .page-number:after { content: counter(page); }
    </style>
    </head>
    <body>
    <header>
        <table class="nust-header-table">
            <tr>
                <!-- Left: Logo -->
                <td class="nust-logo-cell">
                    <img src="' . $logoPath . '" alt="Logo" style="width: 85px; height: auto;">
                </td>
                
                <!-- Right: Text Content -->
                <td class="nust-content-cell">
                    <!-- 1. University Name -->
                    <div class="nust-title">National University of Science and Technology</div>
                    
                    <!-- 2. Two-Column Address & Contact Info -->
                    <table class="nust-details-table">
                        <tr>
                            <!-- Address -->
                            <td style="width: 50%; vertical-align: top;">
                                Cnr Gwanda Road/Cecil Avenue,<br>
                                P.O. Box AC 939<br>
                                Ascot, Bulawayo, Zimbabwe<br>
                                <a href="http://www.nust.ac.zw" style="text-decoration:none;color:#000;">www.nust.ac.zw</a>
                            </td>
                            
                            <!-- Contact Details -->
                            <td style="width: 50%; vertical-align: top; text-align:left; padding-left:20px;">
                                <strong>Telephones:</strong> +263-292-282842<br/>
                                <strong>Ext:</strong> 2362 or 2392<br/>
                                <strong>Fax:</strong> +263-292-286803<br/>
                                <strong>Email:</strong> admissions@nust.ac.zw<br/>
                                <strong>Facebook:</strong> @NUST.ZIM <strong>Twitter:</strong> @nustzim
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </header>

    <footer>
        <div class="footer-wrap">
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <div>Generated on ' . $today . '</div>
                <div>Page <span class="page-number"></span></div>
            </div>
        </div>
    </footer>

    <main>
        <div class="main-title">' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</div>
        <div class="course-line">' . htmlspecialchars($courseLabel, ENT_QUOTES, 'UTF-8') . '</div>
        <div class="prog-line">' . htmlspecialchars($progLabel, ENT_QUOTES, 'UTF-8') . '</div>
        <div class="meta-compact">' . htmlspecialchars($compactMeta, ENT_QUOTES, 'UTF-8') . '</div>

        <br>

        <table class="results">
            <thead>
                <tr>
                    <th style="width:22px;">NO.</th>
                    <th style="width:70px;">STUDENT NO</th>
                    <th style="width:220px;">STUDENT NAME</th>' .
        $headerMarksCols . '
                </tr>
            </thead>
            <tbody>' .
        $tableRowsHtml . '
            </tbody>
        </table>

        <div class="legend-block">
            <strong>Posting Key:</strong>
            NP = Not Posted (no marks),
            CW = Coursework Only,
            EX = Exam Only,
            CX = Coursework &amp; Exam (both posted).<br/>
            <strong>Board Stage Key:</strong>
            NS = Not Submitted,
            LEC = Lecturer Submitted,
            Dept. = Department Board,
            Fac. = Faculty Board,
            Aca. = Academic Board,
            Published = Published / Final.
        </div>
    </main>
    </body></html>';

    require_once __DIR__ . '/../../vendor/autoload.php';
    $dompdf = new \Dompdf\Dompdf();
    $opts   = $dompdf->getOptions();
    $opts->setChroot("/var/www/html/");
    $opts->set('isRemoteEnabled', true);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    $dir = "/var/www/html/datastore/output/results/";
    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }

    $fileLabel = $meta['course_code'] . '_' . $meta['programme_short'] . '_P' . $meta['yearofstudy'] . '_S' . $meta['semester'] . '_' . $meta['academicyear'];
    $filename  = 'RESULTS_' . $slug($fileLabel) . '_' . date('Ymd-His') . '.pdf';

    file_put_contents($dir . $filename, $dompdf->output());

    $base = rtrim($this->core->conf['conf']['path'] ?? '', '/');
    $url  = $base . '/datastore/output/results/' . rawurlencode($filename);

    echo '<script>window.location.href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '";</script>';
    echo '<noscript><meta http-equiv="refresh" content="0; url=' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '"></noscript>';
}






  ///end of export examination







	public function selfExamination() {

		$me = $this->core->userID;
		$this->resultsExamination($me);

	}

	public function createExamination() {
		include $this->core->conf['conf']['classPath'] . "showoptions.inc.php";
		$optionBuilder = new optionBuilder($this->core);

		$programs = $optionBuilder->showStudies();
		include $this->core->conf['conf']['formPath'] . "searchexamslip.form.php";
	}

	public function printExamination() {
		include $this->core->conf['conf']['classPath'] . "showoptions.inc.php";
		$optionBuilder = new optionBuilder($this->core);

		$programs = $optionBuilder->showStudies();
		include $this->core->conf['conf']['formPath'] . "searchexamslip.form.php";
	}


	function listExamination($item) {

		$uid = $_GET['uid'];

		$students = explode(",", $uid);

		foreach($students as $studentid){
			$studentid = trim($studentid);
			$this->resultsExamination($studentid);
		}
	}

	function resultsExamination($item) {

		if(!isset($item) || $this->core->role <= 10){
			$item = $this->core->userID;
		}

		$year = date("Y");
		if(date("m") == 1){				// ALLOW JANUARY EDITS IN PREVIOUS YEAR
			$year = date("Y")-1;
		}

		$semester = '2';
		$studentID = $item;
		$syear = substr($studentID, 0, 4);


		require_once $this->core->conf['conf']['viewPath'] . "payments.view.php";
		$payments = new payments();
		$payments->buildView($this->core);
		$balance = $payments->getBalance($studentID);



		$sql = "SELECT *, `courses`.Name as CourseName FROM `course-electives` 
				LEFT JOIN `periods` ON `periods`.ID = `course-electives`.PeriodID
				LEFT JOIN `billing` ON (`billing`.StudentID = `course-electives`.StudentID AND `course-electives`.PeriodID = `billing`.PeriodID AND (`Approval` != 5 OR `Approval` IS NULL))
				LEFT JOIN `basic-information` ON `course-electives`.StudentID = `basic-information`.ID
				LEFT JOIN `courses` ON `course-electives`.`CourseID` = `courses`.ID
				LEFT JOIN `timetable` ON `courses`.`Name` =  `timetable`.`CourseCode`
				WHERE `course-electives`.StudentID = '$studentID'
				AND `periods`.`Year` = '$year'
				AND `periods`.`Semester`  = '$semester' 
				AND `course-electives`.Approved = 1
				GROUP BY `course-electives`.CourseID
				ORDER BY `ExamDate` DESC";



		$sql = "SELECT *, `courses`.Name as CourseName 
				FROM `course-electives` 
				LEFT JOIN `periods` ON `periods`.ID = `course-electives`.PeriodID
				LEFT JOIN `billing` ON (`billing`.StudentID = `course-electives`.StudentID AND `course-electives`.PeriodID = `billing`.PeriodID AND (`Approval` != 5 OR `Approval` IS NULL))
				LEFT JOIN `basic-information` ON `course-electives`.StudentID = `basic-information`.ID
				LEFT JOIN `courses` ON `course-electives`.`CourseID` = `courses`.ID
				LEFT JOIN `timetable` ON `courses`.`Name` =  `timetable`.`CourseCode`
				WHERE `course-electives`.StudentID = '$studentID'
				AND `periods`.`Year` = '$year'
				AND `periods`.`Semester`  = '$semester' 
				AND `course-electives`.Approved = 1
				AND `courses`.Name IN (SELECT `CourseNo` FROM `grades` WHERE `Grade` IN ('D', 'D+') AND `StudentNo` = '$studentID' AND `grades`.`AcademicYear` = '$year' AND `grades`.`Semester`  = '$semester' )
				GROUP BY `course-electives`.CourseID
				ORDER BY `ExamDate` DESC";


		$sql = "SELECT *, `courses`.Name as CourseName FROM `course-electives` 
				LEFT JOIN `periods` ON `periods`.ID = `course-electives`.PeriodID
				LEFT JOIN `billing` ON (`billing`.StudentID = `course-electives`.StudentID AND `course-electives`.PeriodID = `billing`.PeriodID AND (`Approval` != 5 OR `Approval` IS NULL))
				LEFT JOIN `basic-information` ON `course-electives`.StudentID = `basic-information`.ID
				LEFT JOIN `courses` ON `course-electives`.`CourseID` = `courses`.ID
				LEFT JOIN `timetable` ON `courses`.`Name` =  `timetable`.`CourseCode`
				WHERE `course-electives`.StudentID = '$studentID'
				AND `periods`.`Year` = '$year'
				AND `periods`.`Semester`  = '$semester' 
				AND `course-electives`.Approved = 1
				GROUP BY `course-electives`.CourseID
				ORDER BY `ExamDate` DESC";



		$runx = $this->core->database->doSelectQuery($sql);

		while ($fetchx = $runx->fetch_assoc()){
			$courses .= $fetchx["Name"] . "\n";
		}


		$run = $this->core->database->doSelectQuery($sql);

		$count = 1;
		$currentid = TRUE;
		$total = 0;
		$start = TRUE;

		while ($fetch = $run->fetch_assoc()){

			$id = $fetch["ID"];
			$firstname = $fetch["FirstName"];
			$middlename = $fetch["MiddleName"];
			$surname = $fetch["Surname"];
			$status = $fetch["Status"];
			$sex = $fetch["Sex"];
			$courseid = $fetch["CourseCode"];
			$programno = $fetch["ProgramName"];
			$programid = $fetch["ProgramID"];
			$type = $fetch["ProgramType"];

			$bill = $fetch["PackageName"];




			$nrc = $fetch["GovernmentID"];
			$started = TRUE;
			$studentname = $firstname . " " . $middlename . " " . $surname;
			$examcent = $fetch["ExamCentre"];
			$status = $fetch["Status"];

			$description = $fetch["CourseDescription"];
			$credits = $fetch["CourseCredit"];
			$venue = $fetch['Venue'];
			$lasttrans = $fetch['LastTransaction'];


			if($delivery == "Fulltime"){
				$delivery = "Full Time Student";
			}

			if($status == "Requesting" || $status == "Approved" ){
				$status = "Fully registered";
			} else {
				$status = "NOT FULLY REGISTERED";
			}


			if($bill == "NS-Y-1-S-1"){
				if($balance <= 690){
					$balance = 0;
				}
			}

			if(round($balance) > 0){
				echo'<div class="errorpopup"><center><h1>YOU HAVE NOT PAID ALL FEES. PLEASE ENSURE YOUR BALANCE IS 0 KWACHA OR LOWER. CURRENT BALANCE: K'.$balance.'</h1></center></div>';
				return;
			}

			$status = "100% THRESHOLD MET (K" . number_format($balance,2) .")" ;

			if($start == TRUE){
				// SECURITY
				$rand = rand(100000,999999);

				$owner = $this->core->userID;
				$secname = $studentID . "-".date('Y-m-d')."-".$rand;

				$path = "datastore/output/exam/";
				$filename = $path. $secname .  ".htm";

				require_once $this->core->conf['conf']['classPath'] . "security.inc.php";
				$security = new security();
				$security->buildView($this->core);

				$qrname = $security->qrSecurity($studentID, $studentID, $courses, $studentname, $balance, $nrc);

				$start = FALSE;
			}


			$examdate = $fetch["ExamDate"];
			$time = $fetch["SlotPeriod"];
			$program = $fetch["ProgramName"];
			$delivery = $fetch["StudyType"];

			// BEGIN PRINTING COURSES
			if($currentid == TRUE){
				//echo "hello";

				if (file_exists("datastore/identities/pictures/$studentID.png")) {
					$pic =  '<img width="100%" src="'.$this->core->conf['conf']['path'].'/datastore/identities/pictures/' . $studentID. '.png">';
				} else 	if (file_exists("datastore/identities/pictures/$studentID.png")) {
					$pic =  '<img width="100%" src="'.$this->core->conf['conf']['path'].'/datastore/identities/pictures/' . $studentID. '.png">';
				} else {
					$pic =  '<img width="100%" src="'.$this->core->conf['conf']['path'].'/templates/default/images/noprofile.png">';

					echo'<div class="errorpopup">YOU HAVE NOT TAKEN A PROFILE PICTURE. PLEASE PASS THROUGH THE COMPUTER LAB FOR CAPTURING.</div>';
					//return;
				}


				echo'<div style="width: 850px; height: 700px; position: relative; margin-top: 50px;  margin-bottom: 30px; ">
					<div style="float: left; width: 800px; position: relative; ">
						<div style="position: absolute;  right: 10px; font-size: 10pt; top: 100px;">
							<img  src="/datastore/output/secure/'.$qrname.'.png"><br>'.$secname.'
						</div>
			
						<div style="width: 155px;  padding-left: 30px; float: left;">
							<a href="'. $this->core->conf['conf']['path'] .'">
								<img height="100px" src="'. $this->core->fullTemplatePath .'/images/header.png" />
							</a>
						</div>
						<div style="float: left; font-size: 18pt; color: #000; margin-top: 15px; width: 500px; ">
							
								'.$this->core->conf['conf']['organization'].'
								<div style="font-size: 15pt; font-weight: bold;">FINAL EXAMINATION DOCKET <br>'.$year.' - SEMESTER '.$semester.'</div>
						
						</div>
					</div>
					<div style="width: 800px; margin-left: 20px; margin-top: 20px;">';

				$today = date("d-m-Y");

				echo'<div style="width: 107px; float: left; margin-right: 20px; border: 1px solid #000;"> '. $pic . '</div>';

				echo'<div style="float: left; width: 300px; ">
							Examination slip for: <b>'.$studentname.'</b> 
							<br> StudentID No.: <b>'.$studentID.'</b>
							<br> NRC No.: <b>'.$nrc.'</b>
						</div>
						<div style="float: left; width: 400px;">
							Printed: <b>'.$today.'</b>
							<br> Status: <b>'.$status.'</b>
							<br> Delivery: <b>'.$delivery.'</b>
						</div>
					</div>
					
					<div style="clear: both; width: 800px; margin-left: 20px; padding-top: 20px;">
					 Student is Registered under: <b>'.$bill.'</b><br>
						
						<b>Candidate has been authorized to write FINAL EXAMINATION in the following courses: </b>
					</div>
					<div style="float: left; width: 400px;">
					</div>
					<div style="width: 600px; margin-left: 20px; margin-top: 20px;">';

				$currentid = FALSE;

				echo'<div style="float: left; width: 900px;">
							<div style="float: left; border: 1px solid #ccc; padding: 5px; width: 180px;"><b>DATE / TIME</b></div>
							<div style="float: left; border: 1px solid #ccc; padding: 5px; width: 200px;"><b>VENUE</b></div>
							<div style="float: left; border: 1px solid #ccc; padding: 5px; width: 400px;"><b>COURSE</b></div>
						</div>';

			}


			echo'<div style="float: left; width: 900px;">
				<div style="float: left; border: 1px solid #ccc; padding: 5px; width: 180px; height: 35px;">&nbsp; '.$examdate.' - '.$time.'</div>
				<div style="float: left; border: 1px solid #ccc; padding: 5px; width: 200px; height: 35px;">&nbsp; '.$venue.'</div>
				<div style="float: left; border: 1px solid #ccc; padding: 5px; width: 400px; height: 35px;">&nbsp; <b>'. $fetch["CourseName"] .' - '. $description .'</b></div>
			</div>';

			$count++;
			$isset = TRUE;
			$total = $total+$credits;
		}

		if($isset == TRUE){

			echo '<div style="font-size: 10px; padding-top: 20px; float: left;"> 
				Kindly cross check your courses on this slip against the separate examination timetable for the EXACT date and time of the examination.<br>
				VERY IMPORTANT : Admission into the Examination Hall will be STRICTLY by STUDENT IDENTITY CARD, NRC OR PASSPORT, this EXAMINATION CONFIRMATION SLIP and clearance of all OUTSTANDING TUITION FEES.
				<br><center><button  class="block" style="background-color:green; border-color:blue;height: 40px; width: 150px" size=100 onclick="window.print()">Click To Print Docket</button></center>
			<div>';

			echo '</div>
			</div></div></div>';
			$isset = FALSE;
		} else {
			echo '<div><h1>  You are not registered or your courses are not yet approved. 
						<br> Please see your HOD for approval.</h1></div>';
		}
	}


	private function academicyear($studentNo) {


		echo '<table style="font-size: 11px;">';

		$sql = "SELECT distinct academicyear FROM `grades` WHERE StudentNo = '$studentNo' order by academicyear";

		$run = $this->core->database->doSelectQuery($sql);
		$countyear = 1;
		while ($fetch = $run->fetch_array()){
			print "<tr>\n";
			$acyr = $fetch[0];
			$count = 0;
			$count1 = 0;

			$overallremark= $this->detail($studentNo, $acyr, $countyear, $repeat);
			$remark = $overallremark[0];
			$repeat = $overallremark[1];
			$countyear++;

			//	var_dump($repeat);

			print "</tr>\n\n";
			//<button onclick="window.print()">Print this page</button>
		}

		print "</table>\n";


		return $remark;
	}


	private function detail($studentNo, $acyr, $countyear, $repeat) {

		print "<td>";
		print "$acyr";
		print "&nbsp";
		print "(YEAR $countyear)</td>";
		print "<td>&nbsp&nbsp</td>";

		$sql = "SELECT 
				p1.CourseNo,
				p1.Grade,
				p2.CourseDescription
			FROM 
				`grades` as p1,
				`courses` as p2
			WHERE 	p1.StudentNo = '$studentNo'
			AND	p1.AcademicYear = '$acyr'
			AND	p1.CourseNo = p2.Name  
			ORDER BY p1.courseNo";

		$run = $this->core->database->doSelectQuery($sql);

		$output = "";
		$count2 = 0;
		$countwp=0;
		$suppoutput1="";
		$suppoutput2="";
		$suppoutput3="";
		$countb = 0;
		$i=0;
		$repeatlist = array();

		while ($row = $run->fetch_array()){
			$i++;
			echo "<td>$row[0]</td><td><b>$row[1]</b></td><td>&nbsp&nbsp</td>";
			$count2 = $count2 + 3;

			if ($row[1] == "IN" or $row[1] == "D" or $row[1]=="F" or $row[1]=="NE") {

				$output .= "REPEAT $row[0];";
				if (substr($row[0], -1) =='1'){
					$count=$count + 0.5;
				}else{
					$count=$count + 1;
				}

				$courseno=$row[0];
				$countb=$countb + 1;
				$repeatlist[] =  $row[0];

				$upfail[$i] = $courseno;
			}


			if ($row[1]== "A+" or $row[1]=="A" or $row[1]=="B+" or $row[1]=="B" or $row[1]=="C+" or $row[1]=="C" or $row[1]=="P") {
				$k=$j-1;

				if (substr($row[0], -1) == 1){
					$count1=$count1 + 0.5;
					$count1before=$count1;

					if(count($upfail)>0){
						$count1 = $count1-0.5;
					}

					$checkcount=$count1before-$count1;

					if ($checkcount==1){
						$count=$count-1;
						$count1=$count1+1;
					}

					if ($checkcount==0.5){
						$count=$count-0.5;
						$count1=$count1+0.5;
					}
				} else {
					$count1=$count1 + 1;
					$count1before=$count1;
					if(count($upfail)>0){
						$count1 = $count1-0.5;
					}
					$checkcount=$count1before-$count1;

					if ($checkcount==1){
						$count=$count-1;
						$count1=$count1+1;
					}

					if ($checkcount==0.5){
						$count=$count-0.5;
						$count1=$count1+0.5;
					}
				}
			}

			if ($row[1] == "D+") {

				$suppoutput1 .= "SUPP IN $row[0]; ";
				$suppoutput2 .= "REPEAT $row[0]; ";

				if (substr($row[0], -1) =='1'){
					$count=$count + 0.5;
				}else{
					$count=$count + 1;}
				$countb=$countb + 1;
				$courseno=$row[0];

				$upfail[$i] = $courseno;
			}

			if ($row[1] == "WP") {
				$suppoutput3 .= "DEF IN $row[0];";
				$countwp=$countwp + 1;
			}
			if ($row[1] == "DEF") {
				$suppoutput3 = "DEFFERED";
			}
			if ($row[1] == "EX") {
				$suppoutput3 .= "EXEMPTED IN $row[0]; ";
			}
			if ($row[1] == "DISQ") {
				$suppoutput3 = "DISQUALIFIED";
				$overallremark=="DISQUALIFIED";
			}
			if ($row[1] == "SP") {
				$suppoutput3 = "SUSPENDED";
				$overallremark=="SUSPENDED";
			}
			if ($row[1] == "LT") {
				$suppoutput3 = "EXCLUDE";
				$overallremark="EXCLUDE";
			}
			if ($row[1] == "WH") {
				$suppoutput3 = "WITHHELD";
				$overallremark="WITHHELD";
				$count = 0;
			}

			$year=$row[2];
		}

		while ($count2 < 27) {
			print "<td>&nbsp&nbsp</td>";
			$count2 = $count2 + 1;
		}

		$calcount=$count1/($count+$count1)*100;

		if ($year=='1') {

			if ($calcount < 50) {
				print "<td>EXCLUDE</td>";
				$overallremark="EXCLUDE";
			}else {
				if ($countb == 0) {
					if ($suppoutput3=="") {
						print "<td>CLEAR PASS</td>";
					} else {
						print "$countwp<br> $suppoutput3<br>";
					}

					if ($countwp>2){
						print "2$countwp<br> $suppoutput3<br>";
						print "<td>WITHDRAWN WITH PERMISSION</td>";
					} else {
						print "<td>$suppoutput3</td>";
					}

				}else {
					if ($count1 > 1) {
						$output .= $suppoutput1;
						print "<td>$output</td>";
					}else {
						$output .= $suppoutput2;
						print "<td>$output</td>";
					}
				}
			}

		} else {

			if ($calcount < 75) {
				print "<td>EXCLUDE</td>";
				$overallremark="EXCLUDE";
			} else {


				if ($countb == 0) {
					if ($suppoutput3=="") {
						print "<td>CLEAR PASS</td>";
					} else {
						if ($countwp>2){
							print "<td>WITHDRAWN WITH PERMISSION</td>";
						}else{
							print "<td>$suppoutput3</td>";
						}
					}
				} else {
					if ($count1 > 1) {
						$output .= $suppoutput1;
						print "<td>$output</td>";
					} else {
						$output .= $suppoutput2;
						print "<td>$output</td>";
					}
				}
			}
		}



		if(!empty($upfail)){
			$overallremark="FAILED";
		}


		$ocount=$ocount + $count;

		$out = array($overallremark, $repeatlist);
		return $out;
	}

}

	// END OF THE CURLY BRACKETS 







  
//

public function logsExamination()
{
    $db = $this->core->database;

    $courselecture_id = trim($this->core->cleanGet['courselecture_id'] ?? '');

    if (empty($courselecture_id)) {
        echo '<script>alert("Error: No Class ID provided."); window.close();</script>';
        return;
    }

    // --- SQL LOGIC ---
    // Groups by COALESCE(col, 0) to ensure NULL and 0 are treated as the same unique record
    $sql = "SELECT 
                MAX(created_at) as created_at, 
                user_id, 
                student_id, 
                course_id,
                coursework_id, 
                action_type, 
                target_table,
                COALESCE(cw_mark, 0) as cw_mark,
                COALESCE(other_exam, 0) as other_exam,
                COALESCE(exam_mark, 0) as exam_mark
            FROM assessmentresults_audit_logs 
            WHERE courselecture_id = '" . $db->escape($courselecture_id) . "'
            GROUP BY 
                user_id, 
                student_id, 
                course_id, 
                courselecture_id, 
                coursework_id, 
                action_type, 
                target_table, 
                grade, 
                COALESCE(cw_mark, 0), 
                COALESCE(other_exam, 0), 
                COALESCE(exam_mark, 0), 
                COALESCE(overall_mark, 0)
            ORDER BY created_at ASC 
            LIMIT 2500";

    $result = $db->doSelectQuery($sql);

    $tableRowsHtml = '';
    $rowCount = 0;
    $courseCodeDisplay = "N/A";

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $rowCount++;

            if ($courseCodeDisplay === "N/A" && !empty($row['course_id'])) {
                $courseCodeDisplay = $row['course_id'];
            }

            $actionLabel = 'Unknown';
            if ($row['action_type'] === 'INSERT') {
                $actionLabel = 'New Entry';
            } elseif ($row['action_type'] === 'UPDATE') {
                $actionLabel = 'Modification';
            } elseif ($row['action_type'] === 'DELETE') {
                $actionLabel = 'Removal';
            }

            $targetLabel = $row['target_table'];
            if ($row['target_table'] === 'courseworkresults') {
                $targetLabel = 'Continuous Assessment';
            } elseif ($row['target_table'] === 'courseresultssummary') {
                $targetLabel = 'Final Exam / Summary';
            }

           
            
            $contextParts = [];
            if (!empty($row['student_id'])) {
                $contextParts[] = "<strong>Std:</strong> " . htmlspecialchars($row['student_id']);
            }
            if (!empty($row['coursework_id'])) {
                $contextParts[] = "<strong>Assig:</strong> " . htmlspecialchars($row['coursework_id']);
            } else {
                $contextParts[] = "General Exam";
            }
            $contextStr = implode('<br>', $contextParts);

           
            
            $cwDisplay    = ($row['cw_mark'] == 0) ? '' : htmlspecialchars($row['cw_mark']);
            $otherDisplay = ($row['other_exam'] == 0) ? '' : htmlspecialchars($row['other_exam']);
            $examDisplay  = ($row['exam_mark'] == 0) ? '' : htmlspecialchars($row['exam_mark']);

          
            
            $dateStr = date('d M Y, H:i', strtotime($row['created_at']));

           
            
            $tableRowsHtml .= '
            <tr>
                <td class="c" style="width:4%;">' . $rowCount . '</td>
                <td style="width:13%;">' . $dateStr . '</td>
                <td style="width:12%;">' . htmlspecialchars($row['user_id']) . '</td>
                <td style="width:23%;">' . $contextStr . '</td>
                <td class="c" style="width:7%;">' . $cwDisplay . '</td>
                <td class="c" style="width:7%;">' . $otherDisplay . '</td>
                <td class="c" style="width:7%;">' . $examDisplay . '</td>
                <td style="width:12%;">' . $actionLabel . '</td>
                <td style="width:15%;">' . $targetLabel . '</td>
            </tr>';
        }
    } else {
        $tableRowsHtml = '<tr><td colspan="9" style="text-align:center; padding:20px;">No audit records found.</td></tr>';
    }

 
    
    $logoPath = 'templates/mobile/images/header.png';

    
    $robotoReg  = $this->core->conf['conf']['path'] . '/fonts/Roboto-Regular.ttf';
    $robotoBold = $this->core->conf['conf']['path'] . '/fonts/Roboto-Bold.ttf';

    $today = date('j F Y, H:i');


    
    $courseLabel = "Course Code: " . $courseCodeDisplay;
    $progLabel   = "Class ID: " . $courselecture_id;
    $compactMeta = "Total Records: " . $rowCount;

    $html = '
    <html><head><meta charset="utf-8">
    <style>
        @font-face { font-family: "Roboto"; src: url("' . $robotoReg . '") format("truetype"); font-weight: 400; }
        @font-face { font-family: "Roboto"; src: url("' . $robotoBold . '") format("truetype"); font-weight: 700; }

        @page { margin: 150px 40px 110px 40px; }

        body   { font-family: "Roboto", sans-serif; font-size: 9px; color:#000; }

        header { position: fixed; top: -120px; left: 0; right: 0; height: 110px; }
        footer { position: fixed; bottom: -95px; left:0; right:0; height:95px; }

        .nust-header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 1px solid #bbb; /* muted line */
            padding-bottom: 5px;
            margin-bottom: 10px;
        }
        .nust-logo-cell { width: 15%; vertical-align: top; padding-right: 10px; }
        .nust-content-cell { width: 85%; vertical-align: top; }
        .nust-title {
            font-family: "Times New Roman", serif;
            color: #000;
            font-size: 18px;
            font-weight: bold;
            text-align: left;
            margin-bottom: 8px;
            text-transform: uppercase;
        }
        .nust-details-table {
            width: 100%;
            border-collapse: collapse;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
            color: #000;
            line-height: 1.3;
        }

        .h1  { font-size:14px; font-weight:700; margin:8px 0; text-align:center; text-transform:uppercase; }
        .course-line { font-size:9px; margin-bottom:2px; text-align:center; }
        .prog-line   { font-size:8.5px;  color:#222; margin-bottom:2px; text-align:center; }
        .meta-compact{ font-size:8.5px;  color:#222; margin-bottom:8px; text-align:center; }

        .footer-wrap { width:100%; border-top:1px solid #ddd; padding-top:6px; font-size:7.5px; color:#444; text-align:center; }

        /* ====== BORDERLESS / NO COLOR / SPACED TABLE (like your screenshot) ====== */
        table.results{
            width:100%;
            border-collapse: separate;         /* allow spacing between rows */
            border-spacing: 0 6px;             /* row gap */
            margin-top: 6px;
            font-family: "Roboto", sans-serif;
        }

        table.results thead th{
            background: transparent;           /* no grey */
            color:#000;
            font-size: 8px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .3px;
            text-align: left;
            padding: 2px 4px 6px 4px;
            border: none;
            border-bottom: 1px solid #cfcfcf;  /* muted line under header */
            vertical-align: middle;
            white-space: nowrap;
        }

        table.results tbody td{
            font-size: 8.5px;
            padding: 2px 4px;                  /* airy but clean */
            border: none;                      /* borderless rows */
            vertical-align: top;
        }

        .c{ text-align:center; }
    </style>
    </head>
    <body>
    <header>
        <table class="nust-header-table">
            <tr>
                <td class="nust-logo-cell">
                    <img src="' . $logoPath . '" alt="Logo" style="width: 85px; height: auto;">
                </td>
                <td class="nust-content-cell">
                    <div class="nust-title">National University of Science and Technology</div>

                    <table class="nust-details-table">
                        <tr>
                            <td style="width: 50%; vertical-align: top;">
                                Cnr Gwanda Road/Cecil Avenue,<br>
                                P.O. Box AC 939<br>
                                Ascot, Bulawayo, Zimbabwe<br>
                                <a href="http://www.nust.ac.zw" style="text-decoration:none;color:#000;">www.nust.ac.zw</a>
                            </td>
                            <td style="width: 50%; vertical-align: top; text-align:left; padding-left:20px;">
                                <strong>Telephones:</strong> +263-292-282842<br/>
                                <strong>Ext:</strong> 2362 or 2392<br/>
                                <strong>Fax:</strong> +263-292-286803<br/>
                                <strong>Email:</strong> admissions@nust.ac.zw<br/>
                                <strong>Facebook:</strong> @NUST.ZIM <strong>Twitter:</strong> @nustzim
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </header>

    <footer>
        <div class="footer-wrap">
            Generated on ' . $today . '
        </div>
    </footer>

    <main>
        <h1 class="h1">ASSESSMENT CHANGE LOG REPORT</h1>
        <div class="course-line">' . htmlspecialchars($courseLabel, ENT_QUOTES, "UTF-8") . '</div>
        <div class="prog-line">' . htmlspecialchars($progLabel, ENT_QUOTES, "UTF-8") . '</div>
        <div class="meta-compact">' . htmlspecialchars($compactMeta, ENT_QUOTES, "UTF-8") . '</div>

        <table class="results">
            <thead>
                <tr>
                    <th style="width:4%; text-align:center;">No.</th>
                    <th style="width:13%;">Date & Time</th>
                    <th style="width:12%;">User</th>
                    <th style="width:23%;">Affected Record</th>
                    <th style="width:7%; text-align:center;">OCW</th>
                    <th style="width:7%; text-align:center;">Other</th>
                    <th style="width:7%; text-align:center;">Exam</th>
                    <th style="width:12%;">Type</th>
                    <th style="width:15%;">Area</th>
                </tr>
            </thead>
            <tbody>' .
                $tableRowsHtml . '
            </tbody>
        </table>
    </main>
    </body></html>';

    require_once __DIR__ . '/../../vendor/autoload.php';
    $dompdf = new \Dompdf\Dompdf();
    $dompdf->getOptions()->setChroot("/var/www/html/");

    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    $dir = "/var/www/html/datastore/output/logs/";
    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }

    $filename = 'AuditLog_' . $courselecture_id . '_' . date('Ymd_His') . '.pdf';
    file_put_contents($dir . $filename, $dompdf->output());

    $base = rtrim($this->core->conf['conf']['path'] ?? '', '/');
    $url  = $base . '/datastore/output/logs/' . rawurlencode($filename);

    echo '<script>window.location.href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '";</script>';
    echo '<noscript><meta http-equiv="refresh" content="0; url=' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '"></noscript>';
}






 public function exportExamination($selects)
{
    //  echo 'here'; die();
    $db = $this->core->database;

    
    $selectedclass       = trim($this->core->cleanGet['selectedclass']    ?? ''); // courselecture_id
    $selectedcampus      = trim($this->core->cleanGet['selectedcampus']   ?? '');
    $selectedformat      = trim($this->core->cleanGet['selectedformat']   ?? '');
    $selectedyearofstudy = trim($this->core->cleanGet['yearofstudy']      ?? '');
    $selectedsemester    = trim($this->core->cleanGet['semester']         ?? '');
    $academicyear        = trim($this->core->cleanGet['year']             ?? '');
    $selectedprogID      = trim($this->core->cleanGet['progID']           ?? '');
    $progShortName       = trim($this->core->cleanGet['shortName']        ?? '');
    $programmeName       = trim($this->core->cleanGet['programme']        ?? '');
    $periodID            = trim($this->core->cleanGet['periodID']         ?? '');

    // Helper for filename-safe labels
    $slug = function ($s) {
        $s = preg_replace('/[^A-Za-z0-9\-_.]+/', '-', (string) $s);
        return trim($s, '-');
    };

    // If class missing, tiny info PDF
    if ($selectedclass === '') {
        $today = date('j F, Y');
        $html  = '
        <html><head><meta charset="utf-8">
        <style>
            @page { margin: 70px 40px 70px 40px; }
            body { font-family: Arial, sans-serif; font-size: 11px; }
            header { position: fixed; top: -50px; left:0; right:0; height: 40px; text-align:center; }
            .h1 { font-size: 16px; font-weight: bold; }
            .msg { margin-top: 20px; }
        </style>
        </head>
        <body>
        <header>
            <div class="h1">Class Results Report</div>
        </header>
        <main>
            <div class="msg">A valid class (<code>selectedclass</code>) is required.</div>
            <div class="msg" style="color:#555;">Generated on ' . $today . '</div>
        </main>
        </body></html>';

        require_once __DIR__ . '/../../vendor/autoload.php';
        $dompdf = new \Dompdf\Dompdf();
        $dompdf->getOptions()->setChroot("/var/www/html/");
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $dir = "/var/www/html/datastore/output/results/";
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
        $filename = 'RESULTS_missing-class_' . date('Ymd-His') . '.pdf';
        file_put_contents($dir . $filename, $dompdf->output());

        $base = rtrim($this->core->conf['conf']['path'] ?? '', '/');
        $url  = $base . '/datastore/output/results/' . rawurlencode($filename);

        echo '<script>window.location.href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '";</script>';
        echo '<noscript><meta http-equiv="refresh" content="0; url=' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '"></noscript>';
        return;
    }

   
    
    $selectedclassEsc       = $db->escape($selectedclass);
    $selectedcampusEsc      = $db->escape($selectedcampus);
    $selectedformatEsc      = $db->escape($selectedformat);
    $selectedyearofstudyEsc = $db->escape($selectedyearofstudy);
    $selectedsemesterEsc    = $db->escape($selectedsemester);
    $selectedprogIDEsc      = $db->escape($selectedprogID);
    $periodIDEsc            = $db->escape($periodID);

   
    
    $metaSql = "
        SELECT
            cl.ID            AS courselecture_id,
            cl.year          AS academicyear,
            cl.semester,
            cl.lecturerECno  AS lecturer_id,
            c.Name           AS course_code,
            c.CourseDescription AS course_name,
            s.ShortName      AS programme_short,
            s.Name           AS programme_name
        FROM edurole.courselecture cl
        INNER JOIN edurole.courses c ON c.ID = cl.coursecode
        INNER JOIN edurole.study   s ON s.ID = cl.classcode
        WHERE cl.ID = '{$selectedclassEsc}'
        LIMIT 1
    ";

    $metaRes = $db->doSelectQuery($metaSql);

    $meta = [
        'course_code'     => '',
        'course_name'     => '',
        'programme_short' => $progShortName,
        'programme_name'  => $programmeName,
        'academicyear'    => $academicyear,
        'semester'        => $selectedsemester,
        'yearofstudy'     => $selectedyearofstudy,
        'lecturer_id'     => '',            // will be shown as "Lecturer"
        'campus'          => $selectedcampus,
        'format'          => $selectedformat,
    ];

    if ($metaRes && $metaRes->num_rows) {
        $m                       = $metaRes->fetch_assoc();
        $meta['course_code']     = $m['course_code'] ?? $meta['course_code'];
        $meta['course_name']     = $m['course_name'] ?? $meta['course_name'];
        $meta['programme_short'] = $m['programme_short'] ?? $meta['programme_short'];
        $meta['programme_name']  = $m['programme_name'] ?? $meta['programme_name'];
        $meta['academicyear']    = $m['academicyear'] ?? $meta['academicyear'];
        $meta['semester']        = $m['semester'] ?? $meta['semester'];
        $meta['lecturer_id']     = $m['lecturer_id'] ?? $meta['lecturer_id'];
    }

   
    
    $resultsSql = "
        SELECT
            bi.ID         AS student_id,
            bi.FirstName  AS first_name,
            bi.Surname    AS surname,
            crs.courseWorkMark,
            crs.otherExam,
            crs.finalExaminationMark,
            crs.overallMark,
            crs.resultGrade,
            crs.courseRemark,
            crs.comment,
            crs.board_reviewed,
            crs.created_at  AS captured_at  -- if you have these columns
            -- , crs.captured_by  AS captured_by  -- uncomment + add above
        FROM student_progression sp
        INNER JOIN study s
            ON s.ShortName = sp.programme_code
           AND s.ProgrammesAvailable = 1
           AND s.ID = '{$selectedprogIDEsc}'
        INNER JOIN courselecture cl
            ON cl.classcode = s.ID
           AND cl.ID = '{$selectedclassEsc}'
        INNER JOIN `basic-information` bi
            ON bi.ID = sp.student_id
        LEFT JOIN `student-study-link` `ssl`
            ON `ssl`.StudentID = sp.student_id
        LEFT JOIN edurole.courseresultssummary crs
            ON crs.student_id       = sp.student_id
           AND crs.courselecture_id = cl.ID
        WHERE
            sp.exam_centre = '{$selectedcampusEsc}'
            AND sp.format  = '{$selectedformatEsc}'
            AND sp.periodID = '{$periodIDEsc}'
            AND sp.part    = '{$selectedyearofstudyEsc}'
            AND sp.semester= '{$selectedsemesterEsc}'
    ";

    // ---------- REGULATION FILTER (same logic as screen) ----------
    if (isset($regulationCode, $regYear) && $regulationCode !== '' && (int) $regYear > 0) {
        $regYearInt = (int) $regYear;
        if ($regYearInt >= 2023) {
            $resultsSql .= "
            AND (ssl.regulation_code IS NULL
                 OR RIGHT(ssl.regulation_code, 4) >= '{$regYearInt}')";
        } else {
            $resultsSql .= "
            AND RIGHT(ssl.regulation_code, 4) = '{$regYearInt}'";
        }
    }

    // Finally add ORDER BY
    $resultsSql .= "
       group by      student_id
        ORDER BY bi.Surname, bi.FirstName
    ";

    $res = $db->doSelectQuery($resultsSql);

    $rows = [];
    if ($res && $res->num_rows) {
        $i = 0;
        while ($r = $res->fetch_assoc()) {
            $i++;

            $cw      = $r['courseWorkMark'];
            $other   = $r['otherExam'];
            $exam    = $r['finalExaminationMark'];
            $overall = $r['overallMark'];

            $hasCw      = ($cw !== null && $cw !== '');
            $hasExam    = ($exam !== null && $exam !== '');
            $hasOverall = ($overall !== null && $overall !== '');

            // ------------------------------
            // Posted? (compact code) + Post Type (text)
            // ------------------------------
            $postedCode = 'NP';          // Not Posted
            $postType   = 'No Marks';

            if ($hasCw && !$hasExam) {
                // coursework only  (like -2)
                $postedCode = 'CW';
                $postType   = 'Coursework Only';
            } elseif ($hasCw && $hasExam) {
                // both posted (like 0)
                $postedCode = 'CX';
                $postType   = 'Coursework & Exam';
            } elseif ($hasExam && !$hasCw) {
                $postedCode = 'EX';
                $postType   = 'Exam Only';
            }

           
            
            $boardStage = 'NS'; // Not Submitted
            $br         = $r['board_reviewed'];

            if ($br === null || $br === '' || (int) $br === -1) {
                $boardStage = '--';   // Not Submitted
            } elseif ((int) $br === -2) {
                $boardStage = 'LEC';  // Lecturer Submitted
            } elseif ((int) $br === 0) {
                $boardStage = 'Dept.';   // Department Board
            } elseif ((int) $br === 1 || (int) $br === 2) {
                $boardStage = 'Fac.';   // Faculty Board
            } elseif ((int) $br === 3) {
                $boardStage = 'Aca.';   // Academic Board
            } else {
                $boardStage = 'Published.';  // Published / Final
            }


            
            $capturedAtRaw = $r['captured_at'] ?? '';
            $capturedAt    = '';
            if (!empty($capturedAtRaw)) {
                $ts = strtotime($capturedAtRaw);
                $capturedAt = $ts ? date('Y-m-d', $ts) : $capturedAtRaw;
            }
            $capturedBy = $r['captured_by'] ?? '';

            $nameRaw = trim(($r['first_name'] ?? '') . ' ' . ($r['surname'] ?? ''));
            $name    = strtoupper($nameRaw); 
            $rows[] = [
                'no'          => $i,
                'student_id'  => $r['student_id'],
                'name'        => $name,
                'cw'          => $cw,
                'otherExam'   => $other,
                'exam'        => $exam,
                'overall'     => $overall,
                'grade'       => $r['resultGrade'],
                'remark'      => $r['courseRemark'],
                'comment'     => $r['comment'],
                'postedFlag'  => $postedCode,
                'postType'    => $postType,
                'boardStage'  => $boardStage,
                'captured_at' => $capturedAt,
                'captured_by' => $capturedBy,
            ];
        }
    }

    


    if (!$rows) {
        $today = date('j F, Y');

        $logoPath   = 'templates/mobile/images/header.png';
        $qrPath     = 'templates/mobile/images/verifyaward_qr.png'; 
        $robotoReg  = 'assets/fonts/roboto/Roboto-Regular.ttf';
        $robotoBold = 'assets/fonts/roboto/Roboto-Bold.ttf';

        $html = '
        <html><head><meta charset="utf-8">
        <style>
        @font-face { font-family: "Roboto"; src: url("' . $robotoReg . '") format("truetype"); font-weight: 400; }
        @font-face { font-family: "Roboto"; src: url("' . $robotoBold . '") format("truetype"); font-weight: 700; }

        /* Crucial: Reserve space at the top of the page for the fixed header */
        @page { margin: 150px 40px 110px 40px; }

        body   { font-family: "Roboto", sans-serif; font-size: 11px; color:#000; }

        /* Fixed Header Position */
        header { position: fixed; top: -120px; left: 0; right: 0; height: 110px; }
        footer { position: fixed; bottom: -95px; left:0; right:0; height:95px; }

        /* Main Container Table with Bottom Border */
        .nust-header-table { 
            width: 100%; 
            border-collapse: collapse; 
            border-bottom: 1px solid #999; /* lighter line under the header */
            padding-bottom: 5px; 
            margin-bottom: 10px; 
        }

        /* Logo Column */
        .nust-logo-cell { 
            width: 15%; 
            vertical-align: top; 
            padding-right: 10px; 
        }

        /* Content Column (Title + Address) */
        .nust-content-cell { 
            width: 85%; 
            vertical-align: top; 
        }

        /* University Title Style (Blue, Serif, Uppercase) */
        .nust-title { 
            font-family: "Times New Roman", serif; 
            color: #003366; 
            font-size: 18px; 
            font-weight: bold; 
            text-align: left; 
            margin-bottom: 8px; 
            text-transform: uppercase; 
        }

        /* Address & Contact Info Table */
        .nust-details-table { 
            width: 100%; 
            border-collapse: collapse; 
            font-family: Arial, Helvetica, sans-serif; 
            font-size: 10px; 
            color: #000; 
            line-height: 1.3; 
        }

        .h1  { font-size:16px; font-weight:700; margin:8px 0; text-align:center; }
        .msg { margin-top: 16px; text-align:center; }
        .footer-wrap { width:100%; border-top:1px solid #ddd; padding-top:6px; font-size:7px; color:#444; }
        </style>
        </head>
        <body>
        <header>
            <table class="nust-header-table">
                <tr>
                    <!-- Left: Logo -->
                    <td class="nust-logo-cell">
                        <img src="' . $logoPath . '" alt="Logo" style="width: 85px; height: auto;">
                    </td>
                    
                    <!-- Right: Text Content -->
                    <td class="nust-content-cell">
                        <!-- 1. University Name -->
                        <div class="nust-title">National University of Science and Technology</div>
                        
                        <!-- 2. Two-Column Address & Contact Info -->
                        <table class="nust-details-table">
                            <tr>
                                <!-- Address -->
                                <td style="width: 50%; vertical-align: top;">
                                    Cnr Gwanda Road/Cecil Avenue,<br>
                                    P.O. Box AC 939<br>
                                    Ascot, Bulawayo, Zimbabwe<br>
                                    <a href="http://www.nust.ac.zw" style="text-decoration:none;color:#000;">www.nust.ac.zw</a>
                                </td>
                                
                                <!-- Contact Details -->
                                <td style="width: 50%; vertical-align: top; text-align:left; padding-left:20px;">
                                    <strong>Telephones:</strong> +263-292-282842<br/>
                                    <strong>Ext:</strong> 2362 or 2392<br/>
                                    <strong>Fax:</strong> +263-292-286803<br/>
                                    <strong>Email:</strong> admissions@nust.ac.zw<br/>
                                    <strong>Facebook:</strong> @NUST.ZIM <strong>Twitter:</strong> @nustzim
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </header>

        <footer>
            <div class="footer-wrap">
                Generated on ' . $today . '
            </div>
        </footer>

        <main>
            <h1 class="h1">CLASS RESULTS REPORT</h1>
            <div class="msg">No results found for the selected class.</div>
        </main>
        </body></html>';

        require_once __DIR__ . '/../../vendor/autoload.php';
        $dompdf = new \Dompdf\Dompdf();
        $opts   = $dompdf->getOptions();
        $opts->setChroot("/var/www/html/");
        $opts->set('isRemoteEnabled', true);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $dir = "/var/www/html/datastore/output/results/";
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
        $filename = 'RESULTS_' . $slug($meta['course_code']) . '_empty_' . date('Ymd-His') . '.pdf';
        file_put_contents($dir . $filename, $dompdf->output());

        $base = rtrim($this->core->conf['conf']['path'] ?? '', '/');
        $url  = $base . '/datastore/output/results/' . rawurlencode($filename);

        echo '<script>window.location.href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '";</script>';
        echo '<noscript><meta http-equiv="refresh" content="0; url=' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '"></noscript>';
        return;
    }

    


    $hasOtherExam = false;
    foreach ($rows as $r) {
        if ($r['otherExam'] !== null && $r['otherExam'] !== '') {
            $hasOtherExam = true;
            break;
        }
    }


    $headerMarksCols = '
                    <th style="width:42px;text-align:center;">OCW</th>';
    if ($hasOtherExam) {
        $headerMarksCols .= '
                    <th style="width:42px;text-align:center;">OTHER EXAM</th>';
    }
    $headerMarksCols .= '
                    <th style="width:42px;text-align:center;">EXAM</th>
                    <th style="width:42px;text-align:center;">OM%</th>
                    <th style="width:35px;text-align:center;">GRADE</th>
                    <th style="width:70px;text-align:center;">REMARK</th>
                    <th style="text-align:left;">COMMENT</th>
                    <th style="width:38px;text-align:center;">POSTED?</th>
                    <th style="width:70px;text-align:center;">BOARD STAGE</th>
                    <th style="width:70px;text-align:left;">CAPTURED AT</th>';

  
    $tableRowsHtml = '';
    foreach ($rows as $row) {
        $tableRowsHtml .= '<tr>';
        $tableRowsHtml .= '<td>' . htmlspecialchars($row['no'], ENT_QUOTES, 'UTF-8') . '</td>';
        $tableRowsHtml .= '<td>' . htmlspecialchars($row['student_id'], ENT_QUOTES, 'UTF-8') . '</td>';
        $tableRowsHtml .= '<td>' . htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') . '</td>';

        $tableRowsHtml .= '<td style="text-align:center;">' . htmlspecialchars($row['cw'], ENT_QUOTES, 'UTF-8') . '</td>';

        if ($hasOtherExam) {
            $tableRowsHtml .= '<td style="text-align:center;">' . htmlspecialchars($row['otherExam'], ENT_QUOTES, 'UTF-8') . '</td>';
        }

        $tableRowsHtml .= '<td style="text-align:center;">' . htmlspecialchars($row['exam'], ENT_QUOTES, 'UTF-8') . '</td>';
        $tableRowsHtml .= '<td style="text-align:center;">' . htmlspecialchars($row['overall'], ENT_QUOTES, 'UTF-8') . '</td>';
        $tableRowsHtml .= '<td style="text-align:center;">' . htmlspecialchars($row['grade'], ENT_QUOTES, 'UTF-8') . '</td>';
        $tableRowsHtml .= '<td style="text-align:center;">' . htmlspecialchars($row['remark'], ENT_QUOTES, 'UTF-8') . '</td>';
        $tableRowsHtml .= '<td>' . htmlspecialchars($row['comment'], ENT_QUOTES, 'UTF-8') . '</td>';

        $tableRowsHtml .= '<td style="text-align:center;">' . htmlspecialchars($row['postedFlag'], ENT_QUOTES, 'UTF-8') . '</td>';
        $tableRowsHtml .= '<td style="text-align:center;">' . htmlspecialchars($row['boardStage'], ENT_QUOTES, 'UTF-8') . '</td>';
        $tableRowsHtml .= '<td>' . htmlspecialchars($row['captured_at'], ENT_QUOTES, 'UTF-8') . '</td>';
        $tableRowsHtml .= '</tr>';
    }




    
    $title = "CLASS RESULTS REPORT";

   
    $courseLabel = 'Course: ' . $meta['course_name'] . ' (' . $meta['course_code'] . ')';

   
    $progLabel   = $meta['programme_name'] . '(' . $meta['programme_short'] . ')';

    $compactMeta = 'Part: ' . $meta['yearofstudy']
        . ' | Semester: ' . $meta['semester']
        . ' | ID: ' . $selectedclass
        . ' | Academic Year: ' . $meta['academicyear']
        . ' | Campus: ' . $meta['campus']
        . ' | Format: ' . $meta['format']
        . ' | Lecturer (Captured): ' . $meta['lecturer_id'];

    $today = date('j F, Y');

    $logoPath   = 'templates/mobile/images/header.png';
    $qrPath     = 'templates/mobile/images/verifyaward_qr.png';
    $robotoReg  = 'assets/fonts/roboto/Roboto-Regular.ttf';
    $robotoBold = 'assets/fonts/roboto/Roboto-Bold.ttf';

    $html = '
    <html><head><meta charset="utf-8">
    <style>
    @font-face { font-family: "Roboto"; src: url("' . $robotoReg . '") format("truetype"); font-weight: 400; }
    @font-face { font-family: "Roboto"; src: url("' . $robotoBold . '") format("truetype"); font-weight: 700; }

    /* Crucial: Reserve space at the top of the page for the fixed header */
    @page { margin: 150px 40px 110px 40px; }

    body   { font-family: "Roboto", sans-serif; font-size: 9px; color:#000; }

    /* Fixed Header Position */
    header { position: fixed; top: -120px; left: 0; right: 0; height: 110px; }
    footer { position: fixed; bottom: -95px; left:0; right:0; height:95px; }

    /* Main Container Table with Bottom Border */
    .nust-header-table { 
        width: 100%; 
        border-collapse: collapse; 
        border-bottom: 1px solid #999; /* lighter line under the header */
        padding-bottom: 5px; 
        margin-bottom: 10px; 
    }

    /* Logo Column */
    .nust-logo-cell { 
        width: 15%; 
        vertical-align: top; 
        padding-right: 10px; 
    }

    /* Content Column (Title + Address) */
    .nust-content-cell { 
        width: 85%; 
        vertical-align: top; 
    }

    /* University Title Style (Blue, Serif, Uppercase) */
    .nust-title { 
        font-family: "Times New Roman", serif; 
        color: #003366; 
        font-size: 18px; 
        font-weight: bold; 
        text-align: left; 
        margin-bottom: 8px; 
        text-transform: uppercase; 
    }

    /* Address & Contact Info Table */
    .nust-details-table { 
        width: 100%; 
        border-collapse: collapse; 
        font-family: Arial, Helvetica, sans-serif; 
        font-size: 10px; 
        color: #000; 
        line-height: 1.3; 
    }

    .main-title       { font-size:13px; font-weight:700; margin:2px 0 4px 0; text-transform:uppercase; text-align:center; }
    .course-line      { font-size:8.5px; margin-bottom:2px; text-align:center; }
    .prog-line        { font-size:8px;  color:#444; margin-bottom:2px; text-align:center; }
    .meta-compact     { font-size:7.8px;  color:#444; margin-bottom:4px; text-align:center; }

    table.results { width:100%; border-collapse:collapse; margin-top:4px; font-family:"Roboto",sans-serif; }
    table.results th,
    table.results td { border:none; padding:4px 3px; } /* more vertical padding for visibility */

    table.results th {
        background:#ffffff;
        font-size:7.5px;
        font-weight:700;
        text-align:left;
    }
    table.results td {
        font-size:7px;
    }

    .legend-block {
        margin-top:6px;
        padding-top:3px;
        border-top:0.3px solid #999;
        font-size:7px;
        color:#444;
        page-break-inside:avoid;
    }

    .footer-wrap { width:100%; border-top:1px solid #ddd; padding-top:6px; font-size:7px; color:#444; }
    .page-number:after { content: counter(page); }
    </style>
    </head>
    <body>
    <header>
        <table class="nust-header-table">
            <tr>
                <!-- Left: Logo -->
                <td class="nust-logo-cell">
                    <img src="' . $logoPath . '" alt="Logo" style="width: 85px; height: auto;">
                </td>
                
                <!-- Right: Text Content -->
                <td class="nust-content-cell">
                    <!-- 1. University Name -->
                    <div class="nust-title">National University of Science and Technology</div>
                    
                    <!-- 2. Two-Column Address & Contact Info -->
                    <table class="nust-details-table">
                        <tr>
                            <!-- Address -->
                            <td style="width: 50%; vertical-align: top;">
                                Cnr Gwanda Road/Cecil Avenue,<br>
                                P.O. Box AC 939<br>
                                Ascot, Bulawayo, Zimbabwe<br>
                                <a href="http://www.nust.ac.zw" style="text-decoration:none;color:#000;">www.nust.ac.zw</a>
                            </td>
                            
                            <!-- Contact Details -->
                            <td style="width: 50%; vertical-align: top; text-align:left; padding-left:20px;">
                                <strong>Telephones:</strong> +263-292-282842<br/>
                                <strong>Ext:</strong> 2362 or 2392<br/>
                                <strong>Fax:</strong> +263-292-286803<br/>
                                <strong>Email:</strong> admissions@nust.ac.zw<br/>
                                <strong>Facebook:</strong> @NUST.ZIM <strong>Twitter:</strong> @nustzim
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </header>

    <footer>
        <div class="footer-wrap">
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <div>Generated on ' . $today . '</div>
                <div>Page <span class="page-number"></span></div>
            </div>
        </div>
    </footer>

    <main>
        <div class="main-title">' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</div>
        <div class="course-line">' . htmlspecialchars($courseLabel, ENT_QUOTES, 'UTF-8') . '</div>
        <div class="prog-line">' . htmlspecialchars($progLabel, ENT_QUOTES, 'UTF-8') . '</div>
        <div class="meta-compact">' . htmlspecialchars($compactMeta, ENT_QUOTES, 'UTF-8') . '</div>

        <br>

        <table class="results">
            <thead>
                <tr>
                    <th style="width:22px;">NO.</th>
                    <th style="width:70px;">STUDENT NO</th>
                    <th style="width:220px;">STUDENT NAME</th>' .
        $headerMarksCols . '
                </tr>
            </thead>
            <tbody>' .
        $tableRowsHtml . '
            </tbody>
        </table>

        <div class="legend-block">
            <strong>Posting Key:</strong>
            NP = Not Posted (no marks),
            CW = Coursework Only,
            EX = Exam Only,
            CX = Coursework &amp; Exam (both posted).<br/>
            <strong>Board Stage Key:</strong>
            NS = Not Submitted,
            LEC = Lecturer Submitted,
            Dept. = Department Board,
            Fac. = Faculty Board,
            Aca. = Academic Board,
            Published = Published / Final.
        </div>
    </main>
    </body></html>';

    require_once __DIR__ . '/../../vendor/autoload.php';
    $dompdf = new \Dompdf\Dompdf();
    $opts   = $dompdf->getOptions();
    $opts->setChroot("/var/www/html/");
    $opts->set('isRemoteEnabled', true);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    $dir = "/var/www/html/datastore/output/results/";
    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }

    $fileLabel = $meta['course_code'] . '_' . $meta['programme_short'] . '_P' . $meta['yearofstudy'] . '_S' . $meta['semester'] . '_' . $meta['academicyear'];
    $filename  = 'RESULTS_' . $slug($fileLabel) . '_' . date('Ymd-His') . '.pdf';

    file_put_contents($dir . $filename, $dompdf->output());

    $base = rtrim($this->core->conf['conf']['path'] ?? '', '/');
    $url  = $base . '/datastore/output/results/' . rawurlencode($filename);

    echo '<script>window.location.href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '";</script>';
    echo '<noscript><meta http-equiv="refresh" content="0; url=' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '"></noscript>';
}






  ///end of export examination







	public function selfExamination() {

		$me = $this->core->userID;
		$this->resultsExamination($me);

	}

	public function createExamination() {
		include $this->core->conf['conf']['classPath'] . "showoptions.inc.php";
		$optionBuilder = new optionBuilder($this->core);

		$programs = $optionBuilder->showStudies();
		include $this->core->conf['conf']['formPath'] . "searchexamslip.form.php";
	}

	public function printExamination() {
		include $this->core->conf['conf']['classPath'] . "showoptions.inc.php";
		$optionBuilder = new optionBuilder($this->core);

		$programs = $optionBuilder->showStudies();
		include $this->core->conf['conf']['formPath'] . "searchexamslip.form.php";
	}


	function listExamination($item) {

		$uid = $_GET['uid'];

		$students = explode(",", $uid);

		foreach($students as $studentid){
			$studentid = trim($studentid);
			$this->resultsExamination($studentid);
		}
	}

	function resultsExamination($item) {

		if(!isset($item) || $this->core->role <= 10){
			$item = $this->core->userID;
		}

		$year = date("Y");
		if(date("m") == 1){				// ALLOW JANUARY EDITS IN PREVIOUS YEAR
			$year = date("Y")-1;
		}

		$semester = '2';
		$studentID = $item;
		$syear = substr($studentID, 0, 4);


		require_once $this->core->conf['conf']['viewPath'] . "payments.view.php";
		$payments = new payments();
		$payments->buildView($this->core);
		$balance = $payments->getBalance($studentID);



		$sql = "SELECT *, `courses`.Name as CourseName FROM `course-electives` 
				LEFT JOIN `periods` ON `periods`.ID = `course-electives`.PeriodID
				LEFT JOIN `billing` ON (`billing`.StudentID = `course-electives`.StudentID AND `course-electives`.PeriodID = `billing`.PeriodID AND (`Approval` != 5 OR `Approval` IS NULL))
				LEFT JOIN `basic-information` ON `course-electives`.StudentID = `basic-information`.ID
				LEFT JOIN `courses` ON `course-electives`.`CourseID` = `courses`.ID
				LEFT JOIN `timetable` ON `courses`.`Name` =  `timetable`.`CourseCode`
				WHERE `course-electives`.StudentID = '$studentID'
				AND `periods`.`Year` = '$year'
				AND `periods`.`Semester`  = '$semester' 
				AND `course-electives`.Approved = 1
				GROUP BY `course-electives`.CourseID
				ORDER BY `ExamDate` DESC";



		$sql = "SELECT *, `courses`.Name as CourseName 
				FROM `course-electives` 
				LEFT JOIN `periods` ON `periods`.ID = `course-electives`.PeriodID
				LEFT JOIN `billing` ON (`billing`.StudentID = `course-electives`.StudentID AND `course-electives`.PeriodID = `billing`.PeriodID AND (`Approval` != 5 OR `Approval` IS NULL))
				LEFT JOIN `basic-information` ON `course-electives`.StudentID = `basic-information`.ID
				LEFT JOIN `courses` ON `course-electives`.`CourseID` = `courses`.ID
				LEFT JOIN `timetable` ON `courses`.`Name` =  `timetable`.`CourseCode`
				WHERE `course-electives`.StudentID = '$studentID'
				AND `periods`.`Year` = '$year'
				AND `periods`.`Semester`  = '$semester' 
				AND `course-electives`.Approved = 1
				AND `courses`.Name IN (SELECT `CourseNo` FROM `grades` WHERE `Grade` IN ('D', 'D+') AND `StudentNo` = '$studentID' AND `grades`.`AcademicYear` = '$year' AND `grades`.`Semester`  = '$semester' )
				GROUP BY `course-electives`.CourseID
				ORDER BY `ExamDate` DESC";


		$sql = "SELECT *, `courses`.Name as CourseName FROM `course-electives` 
				LEFT JOIN `periods` ON `periods`.ID = `course-electives`.PeriodID
				LEFT JOIN `billing` ON (`billing`.StudentID = `course-electives`.StudentID AND `course-electives`.PeriodID = `billing`.PeriodID AND (`Approval` != 5 OR `Approval` IS NULL))
				LEFT JOIN `basic-information` ON `course-electives`.StudentID = `basic-information`.ID
				LEFT JOIN `courses` ON `course-electives`.`CourseID` = `courses`.ID
				LEFT JOIN `timetable` ON `courses`.`Name` =  `timetable`.`CourseCode`
				WHERE `course-electives`.StudentID = '$studentID'
				AND `periods`.`Year` = '$year'
				AND `periods`.`Semester`  = '$semester' 
				AND `course-electives`.Approved = 1
				GROUP BY `course-electives`.CourseID
				ORDER BY `ExamDate` DESC";



		$runx = $this->core->database->doSelectQuery($sql);

		while ($fetchx = $runx->fetch_assoc()){
			$courses .= $fetchx["Name"] . "\n";
		}


		$run = $this->core->database->doSelectQuery($sql);

		$count = 1;
		$currentid = TRUE;
		$total = 0;
		$start = TRUE;

		while ($fetch = $run->fetch_assoc()){

			$id = $fetch["ID"];
			$firstname = $fetch["FirstName"];
			$middlename = $fetch["MiddleName"];
			$surname = $fetch["Surname"];
			$status = $fetch["Status"];
			$sex = $fetch["Sex"];
			$courseid = $fetch["CourseCode"];
			$programno = $fetch["ProgramName"];
			$programid = $fetch["ProgramID"];
			$type = $fetch["ProgramType"];

			$bill = $fetch["PackageName"];




			$nrc = $fetch["GovernmentID"];
			$started = TRUE;
			$studentname = $firstname . " " . $middlename . " " . $surname;
			$examcent = $fetch["ExamCentre"];
			$status = $fetch["Status"];

			$description = $fetch["CourseDescription"];
			$credits = $fetch["CourseCredit"];
			$venue = $fetch['Venue'];
			$lasttrans = $fetch['LastTransaction'];


			if($delivery == "Fulltime"){
				$delivery = "Full Time Student";
			}

			if($status == "Requesting" || $status == "Approved" ){
				$status = "Fully registered";
			} else {
				$status = "NOT FULLY REGISTERED";
			}


			if($bill == "NS-Y-1-S-1"){
				if($balance <= 690){
					$balance = 0;
				}
			}

			if(round($balance) > 0){
				echo'<div class="errorpopup"><center><h1>YOU HAVE NOT PAID ALL FEES. PLEASE ENSURE YOUR BALANCE IS 0 KWACHA OR LOWER. CURRENT BALANCE: K'.$balance.'</h1></center></div>';
				return;
			}

			$status = "100% THRESHOLD MET (K" . number_format($balance,2) .")" ;

			if($start == TRUE){
				// SECURITY
				$rand = rand(100000,999999);

				$owner = $this->core->userID;
				$secname = $studentID . "-".date('Y-m-d')."-".$rand;

				$path = "datastore/output/exam/";
				$filename = $path. $secname .  ".htm";

				require_once $this->core->conf['conf']['classPath'] . "security.inc.php";
				$security = new security();
				$security->buildView($this->core);

				$qrname = $security->qrSecurity($studentID, $studentID, $courses, $studentname, $balance, $nrc);

				$start = FALSE;
			}


			$examdate = $fetch["ExamDate"];
			$time = $fetch["SlotPeriod"];
			$program = $fetch["ProgramName"];
			$delivery = $fetch["StudyType"];

			// BEGIN PRINTING COURSES
			if($currentid == TRUE){
				//echo "hello";

				if (file_exists("datastore/identities/pictures/$studentID.png")) {
					$pic =  '<img width="100%" src="'.$this->core->conf['conf']['path'].'/datastore/identities/pictures/' . $studentID. '.png">';
				} else 	if (file_exists("datastore/identities/pictures/$studentID.png")) {
					$pic =  '<img width="100%" src="'.$this->core->conf['conf']['path'].'/datastore/identities/pictures/' . $studentID. '.png">';
				} else {
					$pic =  '<img width="100%" src="'.$this->core->conf['conf']['path'].'/templates/default/images/noprofile.png">';

					echo'<div class="errorpopup">YOU HAVE NOT TAKEN A PROFILE PICTURE. PLEASE PASS THROUGH THE COMPUTER LAB FOR CAPTURING.</div>';
					//return;
				}


				echo'<div style="width: 850px; height: 700px; position: relative; margin-top: 50px;  margin-bottom: 30px; ">
					<div style="float: left; width: 800px; position: relative; ">
						<div style="position: absolute;  right: 10px; font-size: 10pt; top: 100px;">
							<img  src="/datastore/output/secure/'.$qrname.'.png"><br>'.$secname.'
						</div>
			
						<div style="width: 155px;  padding-left: 30px; float: left;">
							<a href="'. $this->core->conf['conf']['path'] .'">
								<img height="100px" src="'. $this->core->fullTemplatePath .'/images/header.png" />
							</a>
						</div>
						<div style="float: left; font-size: 18pt; color: #000; margin-top: 15px; width: 500px; ">
							
								'.$this->core->conf['conf']['organization'].'
								<div style="font-size: 15pt; font-weight: bold;">FINAL EXAMINATION DOCKET <br>'.$year.' - SEMESTER '.$semester.'</div>
						
						</div>
					</div>
					<div style="width: 800px; margin-left: 20px; margin-top: 20px;">';

				$today = date("d-m-Y");

				echo'<div style="width: 107px; float: left; margin-right: 20px; border: 1px solid #000;"> '. $pic . '</div>';

				echo'<div style="float: left; width: 300px; ">
							Examination slip for: <b>'.$studentname.'</b> 
							<br> StudentID No.: <b>'.$studentID.'</b>
							<br> NRC No.: <b>'.$nrc.'</b>
						</div>
						<div style="float: left; width: 400px;">
							Printed: <b>'.$today.'</b>
							<br> Status: <b>'.$status.'</b>
							<br> Delivery: <b>'.$delivery.'</b>
						</div>
					</div>
					
					<div style="clear: both; width: 800px; margin-left: 20px; padding-top: 20px;">
					 Student is Registered under: <b>'.$bill.'</b><br>
						
						<b>Candidate has been authorized to write FINAL EXAMINATION in the following courses: </b>
					</div>
					<div style="float: left; width: 400px;">
					</div>
					<div style="width: 600px; margin-left: 20px; margin-top: 20px;">';

				$currentid = FALSE;

				echo'<div style="float: left; width: 900px;">
							<div style="float: left; border: 1px solid #ccc; padding: 5px; width: 180px;"><b>DATE / TIME</b></div>
							<div style="float: left; border: 1px solid #ccc; padding: 5px; width: 200px;"><b>VENUE</b></div>
							<div style="float: left; border: 1px solid #ccc; padding: 5px; width: 400px;"><b>COURSE</b></div>
						</div>';

			}


			echo'<div style="float: left; width: 900px;">
				<div style="float: left; border: 1px solid #ccc; padding: 5px; width: 180px; height: 35px;">&nbsp; '.$examdate.' - '.$time.'</div>
				<div style="float: left; border: 1px solid #ccc; padding: 5px; width: 200px; height: 35px;">&nbsp; '.$venue.'</div>
				<div style="float: left; border: 1px solid #ccc; padding: 5px; width: 400px; height: 35px;">&nbsp; <b>'. $fetch["CourseName"] .' - '. $description .'</b></div>
			</div>';

			$count++;
			$isset = TRUE;
			$total = $total+$credits;
		}

		if($isset == TRUE){

			echo '<div style="font-size: 10px; padding-top: 20px; float: left;"> 
				Kindly cross check your courses on this slip against the separate examination timetable for the EXACT date and time of the examination.<br>
				VERY IMPORTANT : Admission into the Examination Hall will be STRICTLY by STUDENT IDENTITY CARD, NRC OR PASSPORT, this EXAMINATION CONFIRMATION SLIP and clearance of all OUTSTANDING TUITION FEES.
				<br><center><button  class="block" style="background-color:green; border-color:blue;height: 40px; width: 150px" size=100 onclick="window.print()">Click To Print Docket</button></center>
			<div>';

			echo '</div>
			</div></div></div>';
			$isset = FALSE;
		} else {
			echo '<div><h1>  You are not registered or your courses are not yet approved. 
						<br> Please see your HOD for approval.</h1></div>';
		}
	}


	private function academicyear($studentNo) {


		echo '<table style="font-size: 11px;">';

		$sql = "SELECT distinct academicyear FROM `grades` WHERE StudentNo = '$studentNo' order by academicyear";

		$run = $this->core->database->doSelectQuery($sql);
		$countyear = 1;
		while ($fetch = $run->fetch_array()){
			print "<tr>\n";
			$acyr = $fetch[0];
			$count = 0;
			$count1 = 0;

			$overallremark= $this->detail($studentNo, $acyr, $countyear, $repeat);
			$remark = $overallremark[0];
			$repeat = $overallremark[1];
			$countyear++;

			//	var_dump($repeat);

			print "</tr>\n\n";
			//<button onclick="window.print()">Print this page</button>
		}

		print "</table>\n";


		return $remark;
	}


	private function detail($studentNo, $acyr, $countyear, $repeat) {

		print "<td>";
		print "$acyr";
		print "&nbsp";
		print "(YEAR $countyear)</td>";
		print "<td>&nbsp&nbsp</td>";

		$sql = "SELECT 
				p1.CourseNo,
				p1.Grade,
				p2.CourseDescription
			FROM 
				`grades` as p1,
				`courses` as p2
			WHERE 	p1.StudentNo = '$studentNo'
			AND	p1.AcademicYear = '$acyr'
			AND	p1.CourseNo = p2.Name  
			ORDER BY p1.courseNo";

		$run = $this->core->database->doSelectQuery($sql);

		$output = "";
		$count2 = 0;
		$countwp=0;
		$suppoutput1="";
		$suppoutput2="";
		$suppoutput3="";
		$countb = 0;
		$i=0;
		$repeatlist = array();

		while ($row = $run->fetch_array()){
			$i++;
			echo "<td>$row[0]</td><td><b>$row[1]</b></td><td>&nbsp&nbsp</td>";
			$count2 = $count2 + 3;

			if ($row[1] == "IN" or $row[1] == "D" or $row[1]=="F" or $row[1]=="NE") {

				$output .= "REPEAT $row[0];";
				if (substr($row[0], -1) =='1'){
					$count=$count + 0.5;
				}else{
					$count=$count + 1;
				}

				$courseno=$row[0];
				$countb=$countb + 1;
				$repeatlist[] =  $row[0];

				$upfail[$i] = $courseno;
			}


			if ($row[1]== "A+" or $row[1]=="A" or $row[1]=="B+" or $row[1]=="B" or $row[1]=="C+" or $row[1]=="C" or $row[1]=="P") {
				$k=$j-1;

				if (substr($row[0], -1) == 1){
					$count1=$count1 + 0.5;
					$count1before=$count1;

					if(count($upfail)>0){
						$count1 = $count1-0.5;
					}

					$checkcount=$count1before-$count1;

					if ($checkcount==1){
						$count=$count-1;
						$count1=$count1+1;
					}

					if ($checkcount==0.5){
						$count=$count-0.5;
						$count1=$count1+0.5;
					}
				} else {
					$count1=$count1 + 1;
					$count1before=$count1;
					if(count($upfail)>0){
						$count1 = $count1-0.5;
					}
					$checkcount=$count1before-$count1;

					if ($checkcount==1){
						$count=$count-1;
						$count1=$count1+1;
					}

					if ($checkcount==0.5){
						$count=$count-0.5;
						$count1=$count1+0.5;
					}
				}
			}

			if ($row[1] == "D+") {

				$suppoutput1 .= "SUPP IN $row[0]; ";
				$suppoutput2 .= "REPEAT $row[0]; ";

				if (substr($row[0], -1) =='1'){
					$count=$count + 0.5;
				}else{
					$count=$count + 1;}
				$countb=$countb + 1;
				$courseno=$row[0];

				$upfail[$i] = $courseno;
			}

			if ($row[1] == "WP") {
				$suppoutput3 .= "DEF IN $row[0];";
				$countwp=$countwp + 1;
			}
			if ($row[1] == "DEF") {
				$suppoutput3 = "DEFFERED";
			}
			if ($row[1] == "EX") {
				$suppoutput3 .= "EXEMPTED IN $row[0]; ";
			}
			if ($row[1] == "DISQ") {
				$suppoutput3 = "DISQUALIFIED";
				$overallremark=="DISQUALIFIED";
			}
			if ($row[1] == "SP") {
				$suppoutput3 = "SUSPENDED";
				$overallremark=="SUSPENDED";
			}
			if ($row[1] == "LT") {
				$suppoutput3 = "EXCLUDE";
				$overallremark="EXCLUDE";
			}
			if ($row[1] == "WH") {
				$suppoutput3 = "WITHHELD";
				$overallremark="WITHHELD";
				$count = 0;
			}

			$year=$row[2];
		}

		while ($count2 < 27) {
			print "<td>&nbsp&nbsp</td>";
			$count2 = $count2 + 1;
		}

		$calcount=$count1/($count+$count1)*100;

		if ($year=='1') {

			if ($calcount < 50) {
				print "<td>EXCLUDE</td>";
				$overallremark="EXCLUDE";
			}else {
				if ($countb == 0) {
					if ($suppoutput3=="") {
						print "<td>CLEAR PASS</td>";
					} else {
						print "$countwp<br> $suppoutput3<br>";
					}

					if ($countwp>2){
						print "2$countwp<br> $suppoutput3<br>";
						print "<td>WITHDRAWN WITH PERMISSION</td>";
					} else {
						print "<td>$suppoutput3</td>";
					}

				}else {
					if ($count1 > 1) {
						$output .= $suppoutput1;
						print "<td>$output</td>";
					}else {
						$output .= $suppoutput2;
						print "<td>$output</td>";
					}
				}
			}

		} else {

			if ($calcount < 75) {
				print "<td>EXCLUDE</td>";
				$overallremark="EXCLUDE";
			} else {


				if ($countb == 0) {
					if ($suppoutput3=="") {
						print "<td>CLEAR PASS</td>";
					} else {
						if ($countwp>2){
							print "<td>WITHDRAWN WITH PERMISSION</td>";
						}else{
							print "<td>$suppoutput3</td>";
						}
					}
				} else {
					if ($count1 > 1) {
						$output .= $suppoutput1;
						print "<td>$output</td>";
					} else {
						$output .= $suppoutput2;
						print "<td>$output</td>";
					}
				}
			}
		}



		if(!empty($upfail)){
			$overallremark="FAILED";
		}


		$ocount=$ocount + $count;

		$out = array($overallremark, $repeatlist);
		return $out;
	}

}
?>
