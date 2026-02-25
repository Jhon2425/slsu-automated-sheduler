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
                'course_code' => 'CPT02a',
                'subject_name' => 'Fundamentals of Electricity and Electronics',
                'lec' => 2,
                'lab' => 1,
                'units' => 3,
                'pre_req' => null,
                'year_level' => 1,
                'semester' => 'First Semester'
            ],
            [
                'course_code' => 'CPT04',
                'subject_name' => 'Computer System and Hardware',
                'lec' => 2,
                'lab' => 1,
                'units' => 3,
                'pre_req' => null,
                'year_level' => 1,
                'semester' => 'First Semester'
            ],
            [
                'course_code' => 'CPT05',
                'subject_name' => 'Logic and Switching Theory',
                'lec' => 1,
                'lab' => 1,
                'units' => 2,
                'pre_req' => null,
                'year_level' => 1,
                'semester' => 'First Semester'
            ],
            [
                'course_code' => 'llT01',
                'subject_name' => 'Introduction to Information Technology',
                'lec' => 2,
                'lab' => 1,
                'units' => 3,
                'pre_req' => null,
                'year_level' => 1,
                'semester' => 'First Semester'
            ],
            [
                'course_code' => 'COM01a',
                'subject_name' => 'Computer Programming',
                'lec' => 2,
                'lab' => 1,
                'units' => 3,
                'pre_req' => null,
                'year_level' => 1,
                'semester' => 'First Semester'
            ],
            [
                'course_code' => 'GEC01',
                'subject_name' => 'The Life and Works of Rizal',
                'lec' => 3,
                'lab' => 0,
                'units' => 3,
                'pre_req' => null,
                'year_level' => 1,
                'semester' => 'First Semester'
            ],
            [
                'course_code' => 'GEC04',
                'subject_name' => 'The Contemporary World',
                'lec' => 3,
                'lab' => 0,
                'units' => 3,
                'pre_req' => null,
                'year_level' => 1,
                'semester' => 'First Semester'
            ],
            [
                'course_code' => 'PATHF01',
                'subject_name' => 'Movement Skill Training',
                'lec' => 2,
                'lab' => 0,
                'units' => 2,
                'pre_req' => null,
                'year_level' => 1,
                'semester' => 'First Semester'
            ],
            [
                'course_code' => 'NST01',
                'subject_name' => 'National Service Training Program I',
                'lec' => 3,
                'lab' => 0,
                'units' => 3,
                'pre_req' => null,
                'year_level' => 1,
                'semester' => 'First Semester'
            ],

            // First Year - Second Semester
            [
                'course_code' => 'CPT01',
                'subject_name' => 'Occupational Safety and Health',
                'lec' => 3,
                'lab' => 0,
                'units' => 3, 
                'pre_req' => null,
                'year_level' => 1,
                'semester' => 'Second Semester'
            ],
            [
                'course_code' => 'CPT08a',
                'subject_name' => 'Computer Installation and Servicing',
                'lec' => 2,
                'lab' => 2,
                'units' => 4,
                'pre_req' => 'CPT02a',
                'year_level' => 1,
                'semester' => 'Second Semester'
            ],
            [
                'course_code' => 'ITE15',
                'subject_name' => 'Computer Networking I',
                'lec' => 2,
                'lab' => 1,
                'units' => 3,
                'pre_req' => 'CPT04',
                'year_level' => 1,
                'semester' => 'Second Semester'
            ],
            [
                'course_code' => 'IND01',
                'subject_name' => 'Industrial Drawing',
                'lec' => 1,
                'lab' => 1,
                'units' => 2,
                'pre_req' => null,
                'year_level' => 1,
                'semester' => 'Second Semester'
            ],
            [
                'course_code' => 'GEC02',
                'subject_name' => 'Understanding the Self',
                'lec' => 3,
                'lab' => 0,
                'units' => 3,
                'pre_req' => null,
                'year_level' => 1,
                'semester' => 'Second Semester'
            ],
            [
                'course_code' => 'GEC05',
                'subject_name' => 'Mathematics in the Modern World',
                'lec' => 3,
                'lab' => 0,
                'units' => 3,
                'pre_req' => null,
                'year_level' => 1,
                'semester' => 'Second Semester'
            ],
            [
                'course_code' => 'PATHFIT02',
                'subject_name' => 'Fitness and Wellness Activities',
                'lec' => 2,
                'lab' => 0,
                'units' => 2,
                'pre_req' => 'PATHFIT01',
                'year_level' => 1,
                'semester' => 'Second Semester'
            ],
            [
                'course_code' => 'NST02',
                'subject_name' => 'National Service Training Program 2',
                'lec' => 3,
                'lab' => 0,
                'units' => 3,
                'pre_req' => 'NSTPI',
                'year_level' => 1,
                'semester' => 'Second Semester'
            ],
            // ===================== SECOND YEAR =====================
            // Second Year - First Semester
            [
                'course_code' => 'CPT10',
                'subject_name' => 'Specialized Technology 1',
                'lec' => 2,
                'lab' => 1,
                'units' => 3,
                'pre_req' => 'ITE15',
                'year_level' => 2,
                'semester' => 'First Semester'
            ],
           [
                'course_code' => 'CHM01a',
                'subject_name' => 'Chemistry for Industrial Technologist',
                'lec' => 2,
                'lab' => 1,
                'units' => 3,
                'pre_req' => null,
                'year_level' => 2,
                'semester' => 'First Semester'
            ],
            [
                'course_code' => 'MAT04a',
                'subject_name' => 'Comprehensive Mathematics',
                'lec' => 5,
                'lab' => 0,
                'units' => 5,
                'pre_req' => null,
                'year_level' => 2,
                'semester' => 'First Semester'
            ],
            [
                'course_code' => 'GEC08',
                'subject_name' => 'Science, Technology, and Society',
                'lec' => 3,
                'lab' => 0,
                'units' => 3,
                'pre_req' => null,
                'year_level' => 2,
                'semester' => 'First Semester'
            ],
            [
                'course_code' => 'GEC09',
                'subject_name' => 'Ethics',
                'lec' => 3,
                'lab' => 0,
                'units' => 3,
                'pre_req' => null,
                'year_level' => 2,
                'semester' => 'First Semester'
            ],
            [
                'course_code' => 'GEL07',
                'subject_name' => 'Gender and Society',
                'lec' => 3,
                'lab' => 0,
                'total' => 3,
                'pre_req' => null,
                'year_level' => 2,
                'semester' => 'First Semester'
            ],
            [
                'course_code' => 'GEL10',
                'subject_name' => 'Philippine Popular Culture',
                'lec' => 3,
                'lab' => 0,
                'units' => 3,
                'pre_req' => 'PATHWI2',
                'year_level' => 2,
                'semester' => 'First Semester'
            ],
            [
                'course_code' => 'PATHFIT03',
                'subject_name' => 'Rhythmic Activities and/or Sports',
                'lec' => 2,
                'lab' => 0,
                'units' => 2,
                'pre_req' => 'PATHFIT02',
                'year_level' => 2,
                'semester' => 'First Semester'
            ],


            // Second Year - Second Semester
            [
                'course_code' => 'CPT14',
                'subject_name' => 'Specialized Technology 2',
                'lec' => 2,
                'lab' => 1,
                'units' => 3,
                'pre_req' => 'CPT10',
                'year_level' => 2,
                'semester' => 'Second Semester'
            ],
            [
                'course_code' => 'CPT16',
                'subject_name' => 'Advanced Programming',
                'lec' => 2,
                'lab' => 2,
                'units' => 4,
                'pre_req' => 'COM01a',
                'year_level' => 2,
                'semester' => 'Second Semester'
            ],
            [
                'course_code' => 'PHY01a',
                'subject_name' => 'Physics for Industrial Technologist',
                'lec' => 2,
                'lab' => 1,
                'units' => 3,
                'pre_req' => null,
                'year_level' => 2,
                'semester' => 'Second Semester'
            ],
            [
                'course_code' => 'GEC03',
                'subject_name' => 'Readings in the Philippine History',
                'lec' => 3,
                'lab' => 0,
                'units' => 3,
                'pre_req' => null,
                'year_level' => 2,
                'semester' => 'Second Semester'
            ],
            [
                'course_code' => 'GEC06',
                'subject_name' => 'Purposive Communication',
                'lec' => 3,
                'lab' => 0,
                'units' => 3,
                'pre_req' => null,
                'year_level' => 2,
                'semester' => 'Second Semester'
            ],
            [
                'course_code' => 'GEC07',
                'subject_name' => 'Art Appreciation',
                'lec' => 3,
                'lab' => 0,
                'units' => 3,
                'pre_req' => null,
                'year_level' => 2,
                'semester' => 'Second Semester'
            ],
            [
                'course_code' => 'GEL01',
                'subject_name' => 'Environmental Science',
                'lec' => 3,
                'lab' => 0,
                'units' => 3,
                'pre_req' => null,
                'year_level' => 2,
                'semester' => 'Second Semester'
            ],
            [
                'course_code' => 'PATHFIT04',
                'subject_name' => 'Recreational Activities',
                'lec' => 2,
                'lab' => 0,
                'units' => 2,
                'pre_req' => 'PATHFIT03',
                'year_level' => 2,
                'semester' => 'Second Semester'
            ],

            // ===================== THIRD YEAR =====================
            // Third Year - First Semester
            [
                'course_code' => 'CPT17',
                'subject_name' => 'Embedded System',
                'lec' => 2,
                'lab' => 2,
                'units' => 4,
                'pre_req' => 'CPT16',
                'year_level' => 3,
                'semester' => 'First Semester'
            ],
            [
                'course_code' => 'CPT18',
                'subject_name' => 'Platform Technologies',
                'lec' => 2,
                'lab' => 1,
                'units' => 3,
                'pre_req' => 'CPT16',
                'year_level' => 3,
                'semester' => 'First Semester'
            ],
            [
                'course_code' => 'CPT19',
                'subject_name' => 'Information Management',
                'lec' => 2,
                'lab' => 1,
                'units' => 3,
                'pre_req' => 'CPT16',
                'year_level' => 3,
                'semester' => 'First Semester'
            ],
            [
                'course_code' => 'RES01a',
                'subject_name' => 'Project Study 1 with Intellectual Property Rights',
                'lec' => 2,
                'lab' => 1,
                'units' => 3,
                'pre_req' => null,
                'year_level' => 3,
                'semester' => 'First Semester'
            ],
            [
                'course_code' => 'PSY11a',
                'subject_name' => 'Industrial Psychology',
                'lec' => 3,
                'lab' => 0,
                'units' => 3,
                'pre_req' => null,
                'year_level' => 3,
                'semester' => 'First Semester'
            ],
            [
                'course_code' => 'FLO01',
                'subject_name' => 'Foreign Language',
                'lec' => 3,
                'lab' => 0,
                'units' => 3,
                'pre_req' => null,
                'year_level' => 3,
                'semester' => 'First Semester'
            ],
            [
                'course_code' => 'MTM01',
                'subject_name' => 'Materials Technology Management',
                'lec' => 3,
                'lab' => 0,
                'units' => 3,
                'pre_req' => null,
                'year_level' => 3,
                'semester' => 'First Semester'
            ],

            // Third Year - Second Semester
            [
                'course_code' => 'ITE28',
                'subject_name' => 'Emerging Technologies',
                'lec' => 2,
                'lab' => 1,
                'units' => 3,
                'pre_req' => 'CPT17',
                'year_level' => 3,
                'semester' => 'Second Semester'
            ],
            [
                'course_code' => 'CPT11a',
                'subject_name' => 'Seminar in Computer Technology',
                'lec' => 1,
                'lab' => 1,
                'units' => 2,
                'pre_req' => 'CP116',
                'year_level' => 3,
                'semester' => 'Second Semester'
            ],
            [
                'course_code' => 'RES02a',
                'subject_name' => 'Project Study 2',
                'lec' => 2,
                'lab' => 1,
                'units' => 3,
                'pre_req' => 'RES01a',
                'year_level' => 3,
                'semester' => 'Second Semester'
            ],
            [
                'course_code' => 'ITE13a',
                'subject_name' => 'Professional Issues in Computing',
                'lec' => 3,
                'lab' => 0,
                'units' => 3,
                'pre_req' => null,
                'year_level' => 3,
                'semester' => 'Second Semester'
            ],
            [
                'course_code' => 'IEN08a',
                'subject_name' => 'Quality Control and Assurance',
                'lec' => 3,
                'lab' => 0,
                'units' => 3,
                'pre_req' => null,
                'year_level' => 3,
                'semester' => 'Second Semester'
            ],
            [
                'course_code' => 'IEN04a',
                'subject_name' => 'Industrial Organization and Management',
                'lec' => 3,
                'lab' => 0,
                'units' => 3,
                'pre_req' => null,
                'year_level' => 3,
                'semester' => 'Second Semester'
            ],
            [
                'course_code' => 'MGT02',
                'subject_name' => 'Technopreneurship',
                'lec' => 3,
                'lab' => 0,
                'units' => 3,
                'pre_req' => null,
                'year_level' => 3,
                'semester' => 'Second Semester'
            ],
            [
                'course_code' => 'IEN11a',
                'subject_name' => 'Production Management',
                'lec' => 3,
                'lab' => 0,
                'units' => 3,
                'pre_req' => null,
                'year_level' => 3,
                'semester' => 'Second Semester'
            ],

            // ===================== FOURTH YEAR =====================
            // Fourth Year - First Semester
            [
                'course_code' => 'SIP01',
                'subject_name' => 'Student Internship Program 1 (600 hrs)',
                'lec' => 6,
                'lab' => 0,
                'units' => 6,
                'pre_req' => '4th Year Standing',
                'year_level' => 4,
                'semester' => 'First Semester'
            ],
            // ===================== SECOND YEAR - OLD CURRICULUM =====================
            // First Year - First Semester
            [
                'course_code' => 'CPT05',
                'subject_name' => 'Computer Programming and Operating System ',
                'lec' => 2,
                'lab' => 3,
                'units' => 5,
                'pre_req' => 'CPT03',
                'year_level' => 2,
                'semester' => 'First Semester'
            ],
            [
                'course_code' => 'GEC05',
                'subject_name' => 'Mathematics in the Modern World',
                'lec' => 3,
                'lab' => 0,
                'units' => 3,
                'pre_req' => null,
                'year_level' => 2,
                'semester' => 'First Semester'
            ],
            [
                'course_code' => 'GEC06',
                'subject_name' => 'Purposive Communication',
                'lec' => 3,
                'lab' => 0,
                'units' => 3,
                'pre_req' => null,
                'year_level' => 2,
                'semester' => 'First Semester'
            ],
             [
                'course_code' => 'GEC07',
                'subject_name' => 'Art Appreciation ',
                'lec' => 3,
                'lab' => 0,
                'units' => 3,
                'pre_req' => null,
                'year_level' => 2,
                'semester' => 'First Semester'
            ],
            [
                'course_code' => 'GEC13',
                'subject_name' => 'Individual/Dual Game Sports',
                'lec' => 2,
                'lab' => 0,
                'units' => 2,
                'pre_req' => 'PE001',
                'year_level' => 2,
                'semester' => 'First Semester'
            ],
            // First Year - Second Semester
            [
                'course_code' => 'CPT07',
                'subject_name' => 'Computer Servicing & Installation ',
                'lec' => 2,
                'lab' => 3,
                'units' => 5,
                'pre_req' => 'CPT01',
                'year_level' => 2,
                'semester' => 'Second Semester'
            ],
            [
                'course_code' => 'CPT08',
                'subject_name' => 'Peripheral Interface and Memory System',
                'lec' => 3,
                'lab' => 0,
                'units' => 3,
                'pre_req' => 'CPT02',
                'year_level' => 2,
                'semester' => 'Second Semester'
            ],
            [
                'course_code' => 'GEC08',
                'subject_name' => 'Science, Technology and Society',
                'lec' => 3,
                'lab' => 0,
                'units' => 3,
                'pre_req' => null,
                'year_level' => 2,
                'semester' => 'Second Semester'
            ],
            [
                'course_code' => 'GEC09',
                'subject_name' => 'Ethics',
                'lec' => 3,
                'lab' => 0,
                'units' => 3,
                'pre_req' => null,
                'year_level' => 2,
                'semester' => 'Second Semester'
            ],
            [
                'course_code' => 'GEC10',
                'subject_name' => 'Kontekstwalisadong Komunikasyon sa Filipino',
                'lec' => 3,
                'lab' => 0,
                'units' => 3,
                'pre_req' => null,
                'year_level' => 2,
                'semester' => 'Second Semester'
            ],
            [
                'course_code' => 'GEC11',
                'subject_name' => 'Filipno sa Ibat Ibang Disiplina',
                'lec' => 3,
                'lab' => 0,
                'units' => 3,
                'pre_req' => null,
                'year_level' => 2,
                'semester' => 'Second Semester'
            ],
            [
                'course_code' => 'PE004',
                'subject_name' => 'Team Sports/Games ',
                'lec' => 2,
                'lab' => 0,
                'units' => 2,
                'pre_req' => 'PE001',
                'year_level' => 2,
                'semester' => 'Second Semester'
            ],
            // ===================== THIRD YEAR - OLD CURRICULUM =====================
            // Third Year - First Semester
            [
                'course_code' => 'CPT09',
                'subject_name' => 'Microprocessor, Control System and Instrumentation',
                'lec' => 2,
                'lab' => 3,
                'units' => 5,
                'pre_req' => 'CPT03',
                'year_level' => 3,
                'semester' => 'First Semester'
            ],
             [
                'course_code' => 'CPT10',
                'subject_name' => 'Maintenance, Troubleshooting and Advanced Programming ',
                'lec' => 2,
                'lab' => 3,
                'units' => 5,
                'pre_req' => 'CPT03',
                'year_level' => 3,
                'semester' => 'First Semester'
            ],
            [
                'course_code' => 'NLC01',
                'subject_name' => 'New Labor Code',
                'lec' => 3,
                'lab' => 0,
                'units' => 3,
                'pre_req' => null,
                'year_level' => 3,
                'semester' => 'First Semester'
            ],
            [
                'course_code' => 'SEM01',
                'subject_name' => 'Seminar in Information Trends and Specialized Technology ',
                'lec' => 3,
                'lab' => 0,
                'units' => 3,
                'pre_req' => null,
                'year_level' => 3,
                'semester' => 'First Semester'
            ],
            [
                'course_code' => 'HBO01',
                'subject_name' => 'Human Behavior Organization',
                'lec' => 3,
                'lab' => 0,
                'units' => 3,
                'pre_req' => null,
                'year_level' => 3,
                'semester' => 'First Semester'
            ],
            [
                'course_code' => 'ECM01',
                'subject_name' => 'Entrepreneurial/Cooperative Management',
                'lec' => 3,
                'lab' => 0,
                'units' => 3,
                'pre_req' => null,
                'year_level' => 3,
                'semester' => 'First Semester'
            ],
            // Third Year - Second Semester
            [
                'course_code' => 'CPT11',
                'subject_name' => 'Networking (LAN, WAN, and Internet)',
                'lec' => 2,
                'lab' => 3,
                'units' => 5,
                'pre_req' => 'CPT09,CPT10',
                'year_level' => 3,
                'semester' => 'Second Semester'
            ],
            [
                'course_code' => 'RES01',
                'subject_name' => 'Elements of Research and Project Study ',
                'lec' => 3,
                'lab' => 0,
                'units' => 3,
                'pre_req' => null,
                'year_level' => 3,
                'semester' => 'Second Semester'
            ],
            [
                'course_code' => 'PER01',
                'subject_name' => 'Personnel Administration',
                'lec' => 3,
                'lab' => 0,
                'units' => 3,
                'pre_req' => null,
                'year_level' => 3,
                'semester' => 'Second Semester'
            ],
            [
                'course_code' => 'IPM01',
                'subject_name' => 'Industrial Planning and Material Handling',
                'lec' => 3,
                'lab' => 0,
                'units' => 3,
                'pre_req' => null,
                'year_level' => 3,
                'semester' => 'Second Semester'
            ],
            [
                'course_code' => 'IPM01',
                'subject_name' => 'Industrial Planning and Material Handling',
                'lec' => 3,
                'lab' => 0,
                'units' => 3,
                'pre_req' => null,
                'year_level' => 3,
                'semester' => 'Second Semester'
            ],
            // ===================== FOURTH YEAR - OLD CURRICULUM =====================
            // Fourth Year - First Semester
            [
                'course_code' => 'RES02',
                'subject_name' => 'Research/Project Study and Feasibility Writing ',
                'lec' => 3,
                'lab' => 0,
                'units' => 3,
                'pre_req' => 'RES01',
                'year_level' => 4,
                'semester' => 'First Semester'
            ],
            [
                'course_code' => 'OJT01',
                'subject_name' => 'On-the-Job Training 1 (300 hours)',
                'lec' => 9,
                'lab' => 0,
                'units' => 9,
                'pre_req' => null,
                'year_level' => 4,
                'semester' => 'First Semester'
            ],
            // Fourth Year - Second Semester
            [
                'course_code' => 'OJT02',
                'subject_name' => 'On-the-Job Training 2 (700 hours)',
                'lec' =>15,
                'lab' => 0,
                'units' => 15,
                'pre_req' => 'OJT01',
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