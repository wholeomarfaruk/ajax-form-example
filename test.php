<?php
require BaseDir::getFullPath('config/database.php');

// $classes = Data::fetchAll('classes');

// foreach ($classes as $class) {
//     $class_id = $class['id'];
//     $shift_m = 1;
//     $shift_d = 2;
//     // Data::insert('class_sections', ['class_id','shift_id','section_name'], [$class_id, $shift_d, 'A']);
//     // if($class['id']%2 == 0) {
     
//     //     // Data::insert('class_sections', ['class_id','shift_id','section_name'], [$class_id, $shift_d, 'B']);

//     // } else {
//     //     // Data::insert('class_sections', ['class_id','shift_id','section_name'], [$class_id, $shift_d, 'B']);
//     // }
//     $sections = Data::fetchAll('class_sections', ['class_id = ' . $class_id]);
//     $students_by_section_shift = [
//         'A' => [
//             'morning' => ['Afsana', 'Reshma', 'Muntaha', 'Aysha', 'Nusrat'],
//             'day' => ['Akash', 'Omar', 'Shihab', 'Ratul', 'Faizan']
//         ],
//         'B' => [
//             'morning' => ['Maisha', 'Reshma', 'Muntaha', 'Aysha'],
//             'day' => ['Rihab', 'Mamun', 'Shakil', 'Limon']
//         ],
//         'C' => [
//             'morning' => ['Nusrat', 'Muntaha', 'Riya'],
//             'day' => ['Yeasin', 'Rocky', 'Nayeem']
//         ]
//     ];

//     foreach ($sections as $section) {
//         $section_id = $section['id'];
//         $section_name = $section['section_name'];
//         if (isset($students_by_section_shift[$section_name])) {
//             foreach (['morning' => $shift_m, 'day' => $shift_d] as $shift_key => $shift_id) {
//                 $students = $students_by_section_shift[$section_name][$shift_key];
//                 $values = array_map(function($student) use ($shift_id, $class_id, $section_id) {
//                     return "('$student', '$shift_id', '$class_id', $section_id)";
//                 }, $students);

//                 $sql = mysqli_query($conn, "INSERT INTO students (st_name, shift_id, class_id, section_id) VALUES " . implode(', ', $values));

//                 if ($sql) {
//                     echo "Successfully inserted \n<br>";
//                 }
//             }
//         }
//     }

// }


// $sql="CREATE TABLE results (
//     result_id INT AUTO_INCREMENT PRIMARY KEY,
//     student_id INT NOT NULL,
//     subject_id INT NOT NULL,
//     marks DECIMAL(5, 2) NOT NULL, -- Example: 100.00 max score
//     grade VARCHAR(5), -- Example: A, B, C, etc.
//     exam_type VARCHAR(50), -- Example: Final, Midterm, etc.
//     semester VARCHAR(20), -- Example: Fall 2024
//     result_date DATE DEFAULT CURRENT_DATE,
//     FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
//     FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
// );
// ";

// if (mysqli_query($conn, $sql)) {
//     echo "Table created successfully";
// } else {
//     echo "Error creating table: " . mysqli_error($conn);
// }