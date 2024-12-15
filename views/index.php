<?php
$title = "Home";
?>
<?php
require BaseDir::getFullPath('views/partials/header.php');
?>
<!-- content start -->
<!-- <div class="containter mt-5">
 <h1 class="text-center ">Fetch and Save Student Data Without Page Reload</h1>
 </div> -->
<div class="container border py-3 mt-5">

    <h5>Select Class info for input Result</h5>
    <form action="" method="post" id="student_form">
        <div class="row ">
            <div class="col-md-6">

                <div class="form-floating mb-3">
                    <select class="form-select" id="class" aria-label="Floating label select example" name="class_id">
                        <option value="" selected>Select Class</option>
                        <?php
                        $classes = Data::fetchAll('classes');

                        foreach ($classes as $class) {
                            $id = $class['id'];
                            $class_name = $class['class_name'];
                            echo "<option value='$id'> $class_name </option>";
                        }
                        ?>
                    </select>
                    <label for="floatingSelect">select Class for next action</label>
                </div>




            </div>
            <div class="col-md-6">
                <div class="form-floating mb-3" id="class_shift">
                    <select class="form-select" id="shift" aria-label="Floating label select example" name="shift_id">
                        <option value="" selected>Select Shift</option>


                    </select>
                    <label for="floatingSelect">select Shift for next action</label>
                </div>
            </div>


            <div class="col-md-6">
                <div class="form-floating mb-3" id="class_section">
                    <select class="form-select" id="section" aria-label="Floating label select example"
                        name="section_id">
                        <option value="" selected>Select Section</option>



                    </select>
                    <label for="floatingSelect">select Class for next action</label>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-floating mb-3" id="class_section">
                    <select class="form-select" id="subject" aria-label="Floating label select example"
                        name="subject_id">
                        <option value="" selected>Select Subject</option>


                    </select>
                    <label for="floatingSelect">select Subject for next action</label>
                </div>
            </div>
            <div class="col-md-6">
            <div class="form-floating mb-3" id="class_shift">
                    <select class="form-select" id="semester" name="semester" aria-label="Floating label select example" >
                        <option value="" selected>Select Semester</option>
                        <option value="1st Semester">1st Semester</option>
                        <option value="2nd Semester">2nd Semester</option>
                        <option value="3rd Semester">3rd Semester</option>
                    </select>
                    <label for="floatingSelect">select Semester for next action</label>
                </div>
               
            </div>
            <div class="col-md-6">
            <div class="form-floating mb-3" id="class_shift">
                    <select class="form-select" id="exam_type" name="exam_type" aria-label="Floating label select example" >
                        <option value="" selected>Select Exam type</option>
                        <option value="Weekly">Weekly</option>
                        <option value="Midterm">Midterm</option>
                        <option value="Final">Final</option>


                    </select>
                    <label for="floatingSelect">select Semester for next action</label>
                </div>
               
            </div>
        </div>

</div>
</form>
</div>
<div class="container mb-5 mt-2 border">
    <form action="" method="post" id="student_result_form">
        <table class="table striped">
            <thead>
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Name</th>
                    <th scope="col">Mark</th>
                    <th scope="col">Grade</th>
                </tr>
            </thead>
            <tbody id="table_body">
                <tr>
                   <td class="text-center" colspan="4">Select Class first</td>
                </tr>
            </tbody>

        </table>
    </form>

    <div id="result"></div>
</div>
<!-- content end -->
<?php
require BaseDir::getFullPath('views/partials/footer.php');
?>
<script>
    $(document).ready(function () {
        // Class change event
        $('#class').change(function () {
            var class_id = $(this).val();

            if (class_id === "") {

                $('#shift').empty().append('<option value="">Select Shift</option>');
                callTrigger(); // Trigger shift change to reset it
            } else {
                $.ajax({
                    url: '<?php echo BaseDir::getProjectLink('query/class-shifts'); ?>',
                    type: 'post',
                    data: { class_id: class_id },
                    dataType: 'json',
                    beforeSend: function () {
                        $('#shift').html('<option>Loading...</option>');
                    },
                    success: function (response) {
                        console.log(response);
                        $('#shift').empty().append('<option value="">Select Shift</option>');
                        response.forEach(shift => {
                            $('#shift').append(`<option value="${shift.id}">${shift.shift_name}</option>`);
                        });
                    },
                    error: function (xhr, status, error) {
                        console.error('Error:', error);
                        alert('Failed to load shifts. Please try again.');
                    }
                });
            }
        });

        // Shift change event
        $('#shift').change(function () {
            var class_id = $('#class').val();
            var shift_id = $(this).val();
            console.log(shift_id);
            if (shift_id === "") {
                console.log(shift_id);
                $('#section').empty().append('<option value="">Select Section</option>');
            } else {
                $.ajax({
                    url: '<?php echo BaseDir::getProjectLink('query/class-sections'); ?>',
                    type: 'post',
                    data: { shift_id: shift_id, class_id: class_id },
                    dataType: 'json',
                    beforeSend: function () {
                        $('#section').html('<option>Loading...</option>');
                    },
                    success: function (response) {
                        console.log(response);
                        $('#section').empty().append('<option value="">Select Section</option>');
                        response.forEach(section => {
                            $('#section').append(`<option value="${section.id}">${section.section_name}</option>`);
                        });
                    },
                    error: function (xhr, status, error) {
                        console.error('Error:', error);
                        alert('Failed to load sections. Please try again.');
                    }
                });
            }
        });

        // Section change event
        $('#section').change(function () {
            var section_id = $(this).val();

            if (section_id === "") {
                $('#subject').empty().append('<option value="">Select Subject</option>');
            } else {
                $.ajax({
                    url: '<?php echo BaseDir::getProjectLink('query/class-subjects'); ?>',
                    type: 'post',
                    data: { section_id: section_id },
                    dataType: 'json',
                    beforeSend: function () {
                        $('#subject').html('<option>Loading...</option>');
                    },
                    success: function (response) {
                        console.log(response);
                        $('#subject').empty().append('<option value="">Select Subject</option>');
                        response.forEach(subject => {
                            $('#subject').append(`<option value="${subject.id}">${subject.subject_name}</option>`);
                        });
                    },
                    error: function (xhr, status, error) {
                        console.error('Error:', error);
                        alert('Failed to load subjects. Please try again.');
                    }
                });
            }
        });

        $('#student_form').change(function () {
            var tbody = $('#table_body');
            // Prepare the form data using FormData API
            var $data = new FormData($('#student_form')[0]);

            // Debugging: Check the FormData object
            console.log([...$data.entries()]);

            if ($('#subject').val() === "") {
                $('#result').html('');
            } else {
                $.ajax({
                    url: '<?php echo BaseDir::getProjectLink("query/student-results"); ?>',
                    type: 'POST',
                    data: $data,
                    contentType: false,
                    processData: false,
                    dataType: 'json', // Expect JSON response
                    beforeSend: function () {
                        tbody.append(`<tr><td colspan="4">Loading...</td></tr>`);
                    },
                    success: function (response) {
                        console.log(response); // Debugging: Log the response


                        tbody.empty(); // Clear previous rows

                        // Check if the response contains errors
                        if (response.error) {
                            tbody.append(`<tr><td colspan="4" class="text-danger">${response.error}</td></tr>`);
                        } else if (response.length === 0) {
                            tbody.append(`<tr><td colspan="4">No students found for the given criteria.</td></tr>`);
                        } else {
                            // Loop through each student result and append rows
                            response.forEach(row => {
                                tbody.append(`
                            <tr student_id="${row.student_id}">
                                <th scope="row">${row.student_id}</th>
                                <td>${row.student_name}</td>
                                <td>
                                  
                                    <input type="number" 
                                           class="form-control" 
                                           value="${row.marks}" 
                                           id="mark_${row.student_id}" 
                                           name="mark_${row.student_id}" 
                                           max="100" 
                                           placeholder="Mark">
                                </td>
                                <td>
                                    <input type="text" 
                                           class="form-control" 
                                           value="${row.grade || ''}" 
                                           id="grade_${row.student_id}" 
                                           name="grade_${row.student_id}" 
                                           placeholder="Grade">
                                </td>
                            </tr>
                        `);
                            });
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('Error:', error);
                        alert('Failed to load results. Please try again.');
                    }
                });
            }
        });
        let isProcessing = false; // Prevent recursive calls

$('#student_result_form').on('change', function () {
    if (isProcessing) return; // Exit if already processing
    isProcessing = true;

    // Iterate over each table row in the body
    $('#table_body > tr').each(function () {
        var student_id = $(this).attr('student_id');

        // Safeguard: Ensure the student_id exists
        if (!student_id) return;

        // Get values of mark and grade
        var mark = $('#mark_' + student_id).val();
        var grade = $('#grade_' + student_id).val();
        var subject_id = $('#subject').val();
        var exam_type = $('#exam_type').val();
        var semester = $('#semester').val();
        var class_id = $('#class').val();

        var data = {
            student_id: student_id,
            marks: mark,
            grade: grade,
            subject_id: subject_id,
            exam_type: exam_type,
            semester: semester,
            class_id: class_id
            
        };

        console.log(data);

        // Send data via AJAX
        $.ajax({
            url: '<?php echo BaseDir::getProjectLink("query/store-results"); ?>',
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function (response) {
                console.log('Response:', response);
                showToast("Result updated successfully", type = 'success')
            },
            error: function (xhr, status, error) {
                showToast("Failed to update result", type = 'error')
                console.error('Error:', error);
                alert('Failed to update results. Please try again.');
            }
        });
    
    });

    isProcessing = false; // Reset the flag
});



        // Function to trigger change on #shift
        function callTrigger() {
            $('#shift').trigger('change');
        }
    });

</script>