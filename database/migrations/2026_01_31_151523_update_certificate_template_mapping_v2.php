<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $mapping = [
            "cover" => [
                "date" => ["top" => "73%", "left" => "72%", "color" => "#000", "fontSize" => "11pt"],
                "label_date" => ["top" => "73%", "left" => "65%", "text" => "Tabanan, ", "color" => "#000", "fontSize" => "11pt"],
                "npsn_shine" => ["top" => "40%", "left" => "0", "text" => "NPSN: K997966/IJIN OPERASIONAL: 421.9/0019/DPMPTSP/2022", "color" => "#000", "width" => "100%", "fontSize" => "10pt", "textAlign" => "center"],
                "title_shine" => ["top" => "32%", "left" => "0", "text" => "SHINE EDUCATION BALI", "color" => "#b91c1c", "width" => "100%", "fontSize" => "24pt", "textAlign" => "center", "fontWeight" => "bold"],
                "program_name" => ["top" => "65%", "left" => "0", "color" => "#000", "width" => "100%", "fontSize" => "16pt", "textAlign" => "center", "fontWeight" => "bold"],
                "student_name" => ["top" => "50%", "left" => "0", "color" => "#000", "width" => "100%", "fontSize" => "36pt", "textAlign" => "center", "fontWeight" => "bold"],
                "director_name" => ["top" => "86%", "left" => "60%", "text" => "Ni Putu Sri Indrawati, S.Pd", "color" => "#000", "width" => "30%", "fontSize" => "10pt", "textAlign" => "center", "fontWeight" => "bold"],
                "certificate_no" => ["top" => "11%", "left" => "73%", "color" => "#666", "fontSize" => "10pt"],
                "label_director" => ["top" => "78%", "left" => "60%", "text" => "Director of Shine Education Bali", "color" => "#000", "width" => "30%", "fontSize" => "10pt", "textAlign" => "center", "fontWeight" => "bold"],
                "subtitle_shine" => ["top" => "37%", "left" => "0", "text" => "LEARNING AND TRAINING CENTER", "color" => "#000", "width" => "100%", "fontSize" => "12pt", "textAlign" => "center"],
                "label_completed" => ["top" => "60%", "left" => "0", "text" => "Has successfully completed the", "color" => "#444", "width" => "100%", "fontSize" => "12pt", "textAlign" => "center"],
                "label_presented" => ["top" => "48%", "left" => "0", "text" => "This certificate is proudly presented to", "color" => "#444", "width" => "100%", "fontSize" => "12pt", "textAlign" => "center"],
                "label_and_passed" => ["top" => "70%", "left" => "0", "text" => "Class and passed the Examination.", "color" => "#444", "width" => "100%", "fontSize" => "12pt", "textAlign" => "center"],
                "title_certificate" => ["top" => "15%", "left" => "0", "text" => "CERTIFICATE", "color" => "#b91c1c", "width" => "100%", "fontSize" => "64pt", "textAlign" => "center", "fontWeight" => "bold", "letterSpacing" => "4px"]
            ],
            "result" => [
                "date" => ["top" => "73%", "left" => "72%", "fontSize" => "10pt"],
                "th_avg" => ["top" => "35%", "left" => "70%", "text" => "AVERAGE", "width" => "10%", "border" => "1px solid #ccc", "padding" => "5px", "fontSize" => "10pt", "textAlign" => "center", "fontWeight" => "bold", "backgroundColor" => "#fcd34d"],
                "th_score" => ["top" => "35%", "left" => "50%", "text" => "SCORE", "width" => "10%", "border" => "1px solid #ccc", "padding" => "5px", "fontSize" => "10pt", "textAlign" => "center", "fontWeight" => "bold", "backgroundColor" => "#fcd34d"],
                "th_total" => ["top" => "35%", "left" => "60%", "text" => "TOTAL", "width" => "10%", "border" => "1px solid #ccc", "padding" => "5px", "fontSize" => "10pt", "textAlign" => "center", "fontWeight" => "bold", "backgroundColor" => "#fcd34d"],
                "predicate" => ["top" => "72%", "left" => "0", "width" => "100%", "fontSize" => "14pt", "textAlign" => "center", "fontWeight" => "bold"],
                "label_date" => ["top" => "73%", "left" => "65%", "text" => "Tabanan, ", "color" => "#000", "fontSize" => "10pt"],
                "total_score" => ["top" => "50%", "left" => "60%", "width" => "10%", "fontSize" => "11pt", "textAlign" => "center", "fontWeight" => "bold"],
                "th_materials" => ["top" => "35%", "left" => "25%", "text" => "THE MATERIALS", "color" => "#000", "width" => "25%", "border" => "1px solid #ccc", "padding" => "5px", "fontSize" => "10pt", "textAlign" => "center", "fontWeight" => "bold", "backgroundColor" => "#fcd34d"],
                "title_result" => ["top" => "10%", "left" => "0", "text" => "SHINE EDUCATION BALI", "color" => "#b91c1c", "width" => "100%", "fontSize" => "20pt", "textAlign" => "center", "fontWeight" => "bold"],
                "trainer_name" => ["top" => "86%", "left" => "60%", "text" => "Ni Luh Putu Ari Permata Dewi, S.S.", "color" => "#000", "width" => "30%", "fontSize" => "10pt", "textAlign" => "center", "fontWeight" => "bold"],
                "average_score" => ["top" => "50%", "left" => "70%", "width" => "10%", "fontSize" => "11pt", "textAlign" => "center", "fontWeight" => "bold"],
                "label_grammar" => ["top" => "40%", "left" => "25%", "text" => "Grammar", "width" => "25%", "padding" => "5px", "fontSize" => "11pt"],
                "label_reading" => ["top" => "45%", "left" => "25%", "text" => "Reading", "width" => "25%", "padding" => "5px", "fontSize" => "11pt"],
                "label_trainer" => ["top" => "78%", "left" => "60%", "text" => "Trainer", "color" => "#000", "width" => "30%", "fontSize" => "10pt", "textAlign" => "center", "fontWeight" => "bold"],
                "label_writing" => ["top" => "60%", "left" => "25%", "text" => "Writing", "width" => "25%", "padding" => "5px", "fontSize" => "11pt"],
                "score_grammar" => ["top" => "40%", "left" => "50%", "width" => "10%", "fontSize" => "11pt", "textAlign" => "center"],
                "score_reading" => ["top" => "45%", "left" => "50%", "width" => "10%", "fontSize" => "11pt", "textAlign" => "center"],
                "score_writing" => ["top" => "60%", "left" => "50%", "width" => "10%", "fontSize" => "11pt", "textAlign" => "center"],
                "label_speaking" => ["top" => "50%", "left" => "25%", "text" => "Speaking", "width" => "25%", "padding" => "5px", "fontSize" => "11pt"],
                "score_speaking" => ["top" => "50%", "left" => "50%", "width" => "10%", "fontSize" => "11pt", "textAlign" => "center"],
                "label_listening" => ["top" => "55%", "left" => "25%", "text" => "Listening", "width" => "25%", "padding" => "5px", "fontSize" => "11pt"],
                "label_predicate" => ["top" => "68%", "left" => "0", "text" => "Based on the academic result, student admitted to PASS this level with the predicate", "width" => "100%", "fontSize" => "10pt", "textAlign" => "center"],
                "score_listening" => ["top" => "55%", "left" => "50%", "width" => "10%", "fontSize" => "11pt", "textAlign" => "center"],
                "subtitle_result" => ["top" => "18%", "left" => "0", "text" => "ACADEMIC RESULT", "color" => "#000", "width" => "100%", "fontSize" => "16pt", "textAlign" => "center", "fontWeight" => "bold"]
            ]
        ];

        // Update Template ID 4 or 'Contoh Sertif'
        DB::table('assessment_certificate_templates')
            ->where('id', 4)  // Assuming ID 4 as per conversation
            ->update(['data_mapping' => json_encode($mapping)]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Optional: Revert to empty or previous state
    }
};
