<?php

use App\Models\HomeService;
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
        Schema::create('service_testimonials', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('message');
            $table->string('photo');
            $table->foreignIdFor(HomeService::class)->constrained()->cascadeOnDelete();
            $table->softDeletes();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_testimonials');
    }
};
