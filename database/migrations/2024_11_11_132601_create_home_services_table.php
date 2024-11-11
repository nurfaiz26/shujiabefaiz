<?php

use App\Models\Category;
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
        Schema::create('home_services', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('slug');
            $table->string('thumbnail');
            $table->text('about');
            $table->foreignIdFor(Category::class)->constrained()->cascadeOnDelete();
            $table->boolean('is_popular');
            $table->unsignedBigInteger('price');
            $table->unsignedBigInteger('duration');
            $table->softDeletes();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('home_services');
    }
};
