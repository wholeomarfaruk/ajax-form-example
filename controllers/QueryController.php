<?php 
class QueryController
{
    public function classShifts()
    {
        $class_id = $_POST['class_id'];
        $shifts = Data::fetchAll('class_shift');

        echo json_encode($shifts,JSON_PRETTY_PRINT);
    }
    public function classSections(){
        $class_id = $_POST['class_id'];
        $shift_id = $_POST['shift_id'];
        $sections = Data::fetchAll('class_sections', ['class_id = ' . $class_id, 'shift_id = ' . $shift_id]);

        echo json_encode($sections,JSON_PRETTY_PRINT);
    }
    public function classSubjects(){

        $section_id = $_POST['section_id'];
        $subjects = Data::fetchAll('subjects');

        echo json_encode($subjects,JSON_PRETTY_PRINT);
    }
    public function subjectWiseResults(){

        require BaseDir::getFullPath('config/database.php');
        $subject_id = $_POST['subject_id'];
        $section_id = $_POST['section_id'];
        $class_id = $_POST['class_id'];
        $shift_id = $_POST['shift_id'];
        $exam_type = $_POST['exam_type'];
        $semester = $_POST['semester'];
        
        // Fetch students with matching or non-matching records in results
        $getResult = "
            SELECT 
                st.id AS student_id, 
                st.name AS student_name, 
                rs.result_id, 
                rs.marks, 
                rs.grade,
                rs.exam_type, 
                rs.semester
            FROM 
                students AS st
            LEFT JOIN 
                results AS rs 
            ON 
                st.id = rs.student_id 
                AND rs.subject_id = '$subject_id'
                AND rs.exam_type = '$exam_type'
                AND rs.semester = '$semester'
            WHERE 
                st.class_id = '$class_id' 
                AND st.section_id = '$section_id' 
                AND st.shift_id = '$shift_id'
        ";
        
        // Execute the query
        $results = mysqli_query($conn, $getResult);
        
        // Fetch the data
        $data = [];
        while ($row = mysqli_fetch_assoc($results)) {
            $data[] = $row;
        }
        
        // Output or process the data
        echo json_encode($data);
        
    }
    public function storeResult(){
        require BaseDir::getFullPath('config/database.php');
        $subject_id = $_POST['subject_id'];
        $exam_type = $_POST['exam_type'];
        $semester = $_POST['semester'];
        $student_id = $_POST['student_id'];
        $marks = $_POST['marks'];
        $grade = $_POST['grade'];  
        $class_id = $_POST['class_id']; 

        if(Data::countRecords('results', ['student_id', 'subject_id', 'exam_type', 'semester','class_id'], [$student_id, $subject_id, $exam_type, $semester, $class_id]) > 0){
            $sql = "UPDATE results SET marks = '$marks', grade = '$grade', class_id = '$class_id' WHERE student_id = '$student_id' AND subject_id = '$subject_id' AND exam_type = '$exam_type' AND semester = '$semester' AND class_id = '$class_id'";
            if (mysqli_query($conn, $sql)) {
            $result = [
              'status' => 'success',
              'message' => 'Result updated successfully',
            ];
        }
        }else{
            Data::insert('results', ['student_id', 'subject_id', 'exam_type', 'semester', 'marks', 'grade','class_id'], [$student_id, $subject_id, $exam_type, $semester, $marks, $grade, $class_id]);
        }

        $result = [
            'status' => 'success',
            'message' => 'Result saved successfully',
        ];
        echo json_encode($result);
    }
}