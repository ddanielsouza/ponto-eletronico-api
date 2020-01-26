<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Privilegios extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('privilegios', function (Blueprint $table) {
            $table->increments('id');
            $table->string('descricao', 80);
            $table->string('alias', 50);
            $table->timestamps();
        });

        DB::table('privilegios')->insert([
            'descricao' => 'Super Administrador',
            'alias' => 'super_admin',
        ]);

        DB::table('privilegios')->insert([
            'descricao' => 'Funcionário',
            'alias' => 'funcionario',
        ]);

        Schema::table('users', function (Blueprint $table) {
            $table->integer('privilegio_id')->default(2)->unsigned();
            $table->foreign('privilegio_id')->references('id')->on('privilegios');
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('privilegios');
    }
}
