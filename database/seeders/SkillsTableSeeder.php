<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SkillsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $skills = [
            ['name' => 'Web Development'],
            ['name' => 'Graphic Design'],
            ['name' => 'Content Writing'],
            ['name' => 'SEO'],
            ['name' => 'Digital Marketing'],
            ['name' => 'Mobile App Development'],
            ['name' => 'Data Analysis'],
            ['name' => 'Project Management'],
            ['name' => 'UI/UX Design'],
            ['name' => 'Customer Support'],
            ['name' => 'Copywriting'],
            ['name' => 'Social Media Management'],
            ['name' => 'Video Editing'],
            ['name' => 'Photography'],
            ['name' => 'Translation'],
            ['name' => 'Transcription'],
            ['name' => 'Virtual Assistance'],
            ['name' => 'Accounting'],
            ['name' => 'Financial Analysis'],
            ['name' => 'Consulting'],
            ['name' => 'Legal Services'],
            ['name' => 'Software Testing'],
            ['name' => 'IT Support'],
            ['name' => 'Sales'],
            ['name' => 'Lead Generation'],
            ['name' => 'Business Strategy'],
            ['name' => 'Market Research'],
            ['name' => 'E-commerce Management'],
            ['name' => 'Email Marketing'],
            ['name' => 'Animation'],
            ['name' => '3D Modeling'],
            ['name' => 'Game Development'],
            ['name' => 'Blockchain Development'],
            ['name' => 'Cybersecurity'],
            ['name' => 'Network Administration'],
            ['name' => 'System Administration'],
            ['name' => 'Cloud Computing'],
            ['name' => 'DevOps'],
            ['name' => 'AI/Machine Learning'],
            ['name' => 'Data Entry'],
            ['name' => 'Technical Writing'],
            ['name' => 'Editing and Proofreading'],
            ['name' => 'Voice Over'],
            ['name' => 'Illustration'],
            ['name' => 'Branding'],
            ['name' => 'CRM Management'],
            ['name' => 'Event Planning'],
            ['name' => 'Public Relations'],
            ['name' => 'HTML'],
            ['name' => 'CSS'],
            ['name' => 'JavaScript'],
            ['name' => 'PHP'],
            ['name' => 'Python'],
            ['name' => 'Java'],
            ['name' => 'C#'],
            ['name' => 'C++'],
            ['name' => 'Ruby'],
            ['name' => 'Swift'],
            ['name' => 'Kotlin'],
            ['name' => 'SQL'],
            ['name' => 'NoSQL'],
            ['name' => 'React'],
            ['name' => 'Angular'],
            ['name' => 'Vue.js'],
            ['name' => 'Laravel'],
            ['name' => 'Django'],
            ['name' => 'Flask'],
            ['name' => 'Spring Boot'],
            ['name' => 'Node.js'],
            ['name' => 'Express.js'],
            ['name' => 'ASP.NET'],
            ['name' => 'GraphQL'],
            ['name' => 'RESTful APIs'],
            ['name' => 'Docker'],
            ['name' => 'Kubernetes'],
            ['name' => 'Terraform'],
            ['name' => 'AWS'],
            ['name' => 'Azure'],
            ['name' => 'Google Cloud Platform'],
            ['name' => 'Linux Administration'],
            ['name' => 'Shell Scripting']
        ];

        DB::table('skills')->insert($skills);
    }
}