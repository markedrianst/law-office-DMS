<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up()
    {
        DB::statement("
            ALTER TABLE schedules 
            MODIFY status ENUM(
                'scheduled',
                'completed',
                'cancelled',
                'rescheduled',
                'missed'
            ) DEFAULT 'scheduled'
        ");
    }

    public function down()
    {
        DB::statement("
            ALTER TABLE schedules 
            MODIFY status ENUM(
                'scheduled',
                'completed',
                'cancelled',
                'rescheduled'
            ) DEFAULT 'scheduled'
        ");
    }
};

