<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubjectSeeder extends Seeder
{
    public function run()
    {
        $subjects = [
            // ===================== FIRST YEAR =====================
            // First Semester
            [
                'course_code' => 'GE 1',
                'subject_name' => 'Understanding the Self',
                'lec' => 3,
                'lab' => 0,
                'total' => 3,
                'pre_req' => null,
                'year_level' => 1,
                'semester' => 'First Semester'
            ],
            [
                'course_code' => 'CP 101',
                'subject_name' => 'Fundamentals of Scientific Computing and Information Systems',
                'lec' => 2,
                'lab' => 1,
                'total' => 3,
                'pre_req' => null,
                'year_level' => 1,
                'semester' => 'First Semester'
            ],
            [
                'course_code' => 'CP 102',
                'subject_name' => 'Computer System and Hardware',
                'lec' => 2,
                'lab' => 1,
                'total' => 3,
                'pre_req' => null,
                'year_level' => 1,
                'semester' => 'First Semester'
            ],
            [
                'course_code' => 'ITE1',
                'subject_name' => 'Introduction to Information Technology',
                'lec' => 2,
                'lab' => 1,
                'total' => 3,
                'pre_req' => null,
                'year_level' => 1,
                'semester' => 'First Semester'
            ],
            [
                'course_code' => 'CC101a',
                'subject_name' => 'Computer Programming',
                'lec' => 2,
                'lab' => 1,
                'total' => 3,
                'pre_req' => null,
                'year_level' => 1,
                'semester' => 'First Semester'
            ],
            [
                'course_code' => 'GEC10',
                'subject_name' => 'The Life and Works of Rizal',
                'lec' => 3,
                'lab' => 0,
                'total' => 3,
                'pre_req' => null,
                'year_level' => 1,
                'semester' => 'First Semester'
            ],
            [
                'course_code' => 'GEsOc',
                'subject_name' => 'The Contemporary World',
                'lec' => 3,
                'lab' => 0,
                'total' => 3,
                'pre_req' => null,
                'year_level' => 1,
                'semester' => 'First Semester'
            ],
            [
                'course_code' => 'PATH401',
                'subject_name' => 'Movement Self Testing',
                'lec' => 2,
                'lab' => 0,
                'total' => 2,
                'pre_req' => null,
                'year_level' => 1,
                'semester' => 'First Semester'
            ],
            [
                'course_code' => 'NSTPI',
                'subject_name' => 'National Service Training Program I',
                'lec' => 3,
                'lab' => 0,
                'total' => 3,
                'pre_req' => null,
                'year_level' => 1,
                'semester' => 'First Semester'
            ],

            // First Year - Second Semester
            [
                'course_code' => 'CP103',
                'subject_name' => 'Computer Installation and Operation and Safety and Health',
                'lec' => 2,
                'lab' => 1,
                'total' => 3,
                'pre_req' => 'CP101a, CP102',
                'year_level' => 1,
                'semester' => 'Second Semester'
            ],
            [
                'course_code' => 'CP103b',
                'subject_name' => 'Computer Installation and Operation and Safety and Health',
                'lec' => 2,
                'lab' => 1,
                'total' => 3,
                'pre_req' => 'CP101a, CP102',
                'year_level' => 1,
                'semester' => 'Second Semester'
            ],
            [
                'course_code' => 'IND2l',
                'subject_name' => 'Industrial Drawing',
                'lec' => 1,
                'lab' => 2,
                'total' => 3,
                'pre_req' => null,
                'year_level' => 1,
                'semester' => 'Second Semester'
            ],
            [
                'course_code' => 'GE102',
                'subject_name' => 'Understanding the Self',
                'lec' => 3,
                'lab' => 0,
                'total' => 3,
                'pre_req' => null,
                'year_level' => 1,
                'semester' => 'Second Semester'
            ],
            [
                'course_code' => 'GEsoci',
                'subject_name' => 'Understanding the Self',
                'lec' => 3,
                'lab' => 0,
                'total' => 3,
                'pre_req' => null,
                'year_level' => 1,
                'semester' => 'Second Semester'
            ],
            [
                'course_code' => 'PATHWI2',
                'subject_name' => 'Fitness and Wellness',
                'lec' => 2,
                'lab' => 0,
                'total' => 2,
                'pre_req' => 'PATH401',
                'year_level' => 1,
                'semester' => 'Second Semester'
            ],
            [
                'course_code' => 'NSTP2',
                'subject_name' => 'National Service Training Program 2',
                'lec' => 3,
                'lab' => 0,
                'total' => 3,
                'pre_req' => 'NSTPI',
                'year_level' => 1,
                'semester' => 'Second Semester'
            ],

            // ===================== SECOND YEAR =====================
            // Second Year - First Semester
            [
                'course_code' => 'CP113',
                'subject_name' => 'Database Management System I',
                'lec' => 2,
                'lab' => 1,
                'total' => 3,
                'pre_req' => 'CC101a',
                'year_level' => 2,
                'semester' => 'First Semester'
            ],
            [
                'course_code' => 'CHM1a',
                'subject_name' => 'Calculus for Electrical Technology I',
                'lec' => 2,
                'lab' => 1,
                'total' => 3,
                'pre_req' => null,
                'year_level' => 2,
                'semester' => 'First Semester'
            ],
            [
                'course_code' => 'MATH1',
                'subject_name' => 'General Mathematics',
                'lec' => 3,
                'lab' => 0,
                'total' => 3,
                'pre_req' => null,
                'year_level' => 2,
                'semester' => 'First Semester'
            ],
            [
                'course_code' => 'GEC08',
                'subject_name' => 'Science, Technology, and Society',
                'lec' => 3,
                'lab' => 0,
                'total' => 3,
                'pre_req' => null,
                'year_level' => 2,
                'semester' => 'First Semester'
            ],
            [
                'course_code' => 'GEL10',
                'subject_name' => 'Ethics',
                'lec' => 3,
                'lab' => 0,
                'total' => 3,
                'pre_req' => null,
                'year_level' => 2,
                'semester' => 'First Semester'
            ],
            [
                'course_code' => 'GEL12',
                'subject_name' => 'Quezon and Society',
                'lec' => 3,
                'lab' => 0,
                'total' => 3,
                'pre_req' => null,
                'year_level' => 2,
                'semester' => 'First Semester'
            ],
            [
                'course_code' => 'PATHW113',
                'subject_name' => 'Physical Activities Towards Health & Wellness 3',
                'lec' => 2,
                'lab' => 0,
                'total' => 2,
                'pre_req' => 'PATHWI2',
                'year_level' => 2,
                'semester' => 'First Semester'
            ],

            // Second Year - Second Semester
            [
                'course_code' => 'CP114',
                'subject_name' => 'Advanced Programming',
                'lec' => 2,
                'lab' => 1,
                'total' => 3,
                'pre_req' => 'CC101a',
                'year_level' => 2,
                'semester' => 'Second Semester'
            ],
            [
                'course_code' => 'CPT1b',
                'subject_name' => 'Physics for Computer Technology',
                'lec' => 3,
                'lab' => 0,
                'total' => 3,
                'pre_req' => 'CPT1a',
                'year_level' => 2,
                'semester' => 'Second Semester'
            ],
            [
                'course_code' => 'ITE3a',
                'subject_name' => 'Integrative Programming',
                'lec' => 2,
                'lab' => 1,
                'total' => 3,
                'pre_req' => null,
                'year_level' => 2,
                'semester' => 'Second Semester'
            ],
            [
                'course_code' => 'GE103',
                'subject_name' => 'Readings in the Philippine History',
                'lec' => 3,
                'lab' => 0,
                'total' => 3,
                'pre_req' => null,
                'year_level' => 2,
                'semester' => 'Second Semester'
            ],
            [
                'course_code' => 'GEL06',
                'subject_name' => 'Art Appreciation and Communication',
                'lec' => 3,
                'lab' => 0,
                'total' => 3,
                'pre_req' => null,
                'year_level' => 2,
                'semester' => 'Second Semester'
            ],
            [
                'course_code' => 'GEL01',
                'subject_name' => 'Environmental Science',
                'lec' => 3,
                'lab' => 0,
                'total' => 3,
                'pre_req' => null,
                'year_level' => 2,
                'semester' => 'Second Semester'
            ],
            [
                'course_code' => 'PATHW114',
                'subject_name' => 'Recreational Activities',
                'lec' => 2,
                'lab' => 0,
                'total' => 2,
                'pre_req' => 'PATHW113',
                'year_level' => 2,
                'semester' => 'Second Semester'
            ],

            // ===================== THIRD YEAR =====================
            // Third Year - First Semester
            [
                'course_code' => 'CP115',
                'subject_name' => 'Networking I',
                'lec' => 2,
                'lab' => 1,
                'total' => 3,
                'pre_req' => 'CP113',
                'year_level' => 3,
                'semester' => 'First Semester'
            ],
            [
                'course_code' => 'CP117',
                'subject_name' => 'Embedded System',
                'lec' => 1,
                'lab' => 2,
                'total' => 3,
                'pre_req' => 'CP113',
                'year_level' => 3,
                'semester' => 'First Semester'
            ],
            [
                'course_code' => 'CP116',
                'subject_name' => 'Data Structure',
                'lec' => 2,
                'lab' => 1,
                'total' => 3,
                'pre_req' => 'CP113',
                'year_level' => 3,
                'semester' => 'First Semester'
            ],
            [
                'course_code' => 'CP119',
                'subject_name' => 'Project Based and Intellectual Property Rights',
                'lec' => 3,
                'lab' => 0,
                'total' => 3,
                'pre_req' => 'CP113',
                'year_level' => 3,
                'semester' => 'First Semester'
            ],
            [
                'course_code' => 'ITE3W1a',
                'subject_name' => 'Information Management',
                'lec' => 3,
                'lab' => 0,
                'total' => 3,
                'pre_req' => 'CP113',
                'year_level' => 3,
                'semester' => 'First Semester'
            ],
            [
                'course_code' => 'PSY11a',
                'subject_name' => 'Industrial Psychology',
                'lec' => 3,
                'lab' => 0,
                'total' => 3,
                'pre_req' => null,
                'year_level' => 3,
                'semester' => 'First Semester'
            ],
            [
                'course_code' => 'FIL201',
                'subject_name' => 'Mga Babasahing Pilipino',
                'lec' => 3,
                'lab' => 0,
                'total' => 3,
                'pre_req' => null,
                'year_level' => 3,
                'semester' => 'First Semester'
            ],
            [
                'course_code' => 'MTM61',
                'subject_name' => 'Materials Technology and Technical Drafting',
                'lec' => 3,
                'lab' => 0,
                'total' => 3,
                'pre_req' => null,
                'year_level' => 3,
                'semester' => 'First Semester'
            ],

            // Third Year - Second Semester
            [
                'course_code' => 'CPT1b',
                'subject_name' => 'Emerging Technologies and Scientific Computer Technology',
                'lec' => 2,
                'lab' => 1,
                'total' => 3,
                'pre_req' => 'CPT1a',
                'year_level' => 3,
                'semester' => 'Second Semester'
            ],
            [
                'course_code' => 'CP111a',
                'subject_name' => 'Platform Technologies',
                'lec' => 3,
                'lab' => 0,
                'total' => 3,
                'pre_req' => 'CP116',
                'year_level' => 3,
                'semester' => 'Second Semester'
            ],
            [
                'course_code' => 'IT-BA',
                'subject_name' => 'Capstone Project 2',
                'lec' => 3,
                'lab' => 0,
                'total' => 3,
                'pre_req' => 'ITE3W1a',
                'year_level' => 3,
                'semester' => 'Second Semester'
            ],
            [
                'course_code' => 'ITE3la',
                'subject_name' => 'System Integration and Architecture',
                'lec' => 3,
                'lab' => 0,
                'total' => 3,
                'pre_req' => null,
                'year_level' => 3,
                'semester' => 'Second Semester'
            ],
            [
                'course_code' => 'E1M2a',
                'subject_name' => 'Quality Assurance and Continuous Improvement',
                'lec' => 3,
                'lab' => 0,
                'total' => 3,
                'pre_req' => null,
                'year_level' => 3,
                'semester' => 'Second Semester'
            ],
            [
                'course_code' => 'E1M6s',
                'subject_name' => 'Industrial Organization and Management',
                'lec' => 3,
                'lab' => 0,
                'total' => 3,
                'pre_req' => null,
                'year_level' => 3,
                'semester' => 'Second Semester'
            ],
            [
                'course_code' => 'MTM62',
                'subject_name' => 'Technopreneurship',
                'lec' => 3,
                'lab' => 0,
                'total' => 3,
                'pre_req' => null,
                'year_level' => 3,
                'semester' => 'Second Semester'
            ],
            [
                'course_code' => 'E1M15',
                'subject_name' => 'Production Management',
                'lec' => 3,
                'lab' => 0,
                'total' => 3,
                'pre_req' => null,
                'year_level' => 3,
                'semester' => 'Second Semester'
            ],

            // ===================== FOURTH YEAR =====================
            // Fourth Year - First Semester
            [
                'course_code' => 'SIW1',
                'subject_name' => 'Student Internship 1',
                'lec' => 0,
                'lab' => 0,
                'total' => 6,
                'pre_req' => 'All Subjects in 3rd Year',
                'year_level' => 4,
                'semester' => 'First Semester'
            ],

            // Fourth Year - Second Semester
            [
                'course_code' => 'SIW2',
                'subject_name' => 'Student Internship 2',
                'lec' => 0,
                'lab' => 0,
                'total' => 6,
                'pre_req' => 'SIW1',
                'year_level' => 4,
                'semester' => 'Second Semester'
            ],
        ];

        // Insert all subjects
        DB::table('subjects')->insert($subjects);
    }
}

// =============================================================================
// Don't forget to add this to DatabaseSeeder.php
// =============================================================================

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            SubjectSeeder::class,
            // ... other seeders
        ]);
    }
}