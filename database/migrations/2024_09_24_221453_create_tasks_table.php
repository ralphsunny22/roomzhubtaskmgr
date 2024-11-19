<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('freelancer_id')->nullable(); //the person that eventually did the job

            $table->longText('task_title')->nullable();
            $table->string('task_date_preceed')->nullable(); //on, before, flexible
            $table->string('task_date')->nullable();
            $table->string('task_part_of_day')->nullable(); //morning, mid-day, afternoon evening
            $table->string('task_time_of_day')->nullable(); // before 10am, 10am - 2pm, 2pm - 6pm, after 6pm

            $table->string('task_current_latitude')->nullable();
            $table->string('task_current_longitude')->nullable();
            $table->string('task_current_city')->nullable();
            $table->string('task_current_state')->nullable();
            $table->string('task_current_country')->nullable();
            $table->string('task_current_address')->nullable();

            $table->boolean('is_removal_task')->default(false); // involves transit

            $table->string('pickup_latitude')->nullable();
            $table->string('pickup_longitude')->nullable();
            $table->string('pickup_city')->nullable();
            $table->string('pickup_state')->nullable();
            $table->string('pickup_country')->nullable();
            $table->string('pickup_address')->nullable();

            $table->string('dropoff_latitude')->nullable();
            $table->string('dropoff_longitude')->nullable();
            $table->string('dropoff_city')->nullable();
            $table->string('dropoff_state')->nullable();
            $table->string('dropoff_country')->nullable();
            $table->string('dropoff_address')->nullable();

            $table->boolean('is_done_online')->default(false);
            $table->boolean('is_done_inperson')->default(false);

            $table->longText('task_description')->nullable();

            $table->json('task_images')->nullable();

            $table->float('task_budget')->nullable();
            $table->float('task_agreed_price')->nullable();

            $table->datetime('accepted_at')->nullable(); //when client accepted and paid
            $table->datetime('started_at')->nullable(); //when freelancer started task
            $table->datetime('completed_at')->nullable(); //when freelancer completed task
            $table->datetime('freelancer_cancelled_at')->nullable();
            $table->datetime('client_cancelled_at')->nullable();
            $table->datetime('freelancer_abandoned_at')->nullable(); ////if client cancelled becos freelancer abandoned
            $table->datetime('client_abandoned_at')->nullable(); ////if freelancer cancelled becos client abandoned


            $table->string('status'); //pending, accepted, started, cancelled, abandoned, completed
            //pending, accepted(client has paid), started(freelancer has started task), incompleted(task is started but not completed), cancelled(if done by client) abandoned, completed(task is done)

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
