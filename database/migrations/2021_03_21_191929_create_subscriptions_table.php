<?php

use App\Models\Subscription;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'subscriptions',
            function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('device_id');
                $table->string('token');
                $table->string('receipt');
                $table->enum('status', Subscription::ALL_STATUS);
                $table->string('os', 10);
                $table->dateTime('expired_at');
                $table->timestamps();

                $table->index(['token', 'status', 'expired_at']);

                $table->foreign('device_id')->references('id')->on('devices');
            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('subscriptions');
    }
}
