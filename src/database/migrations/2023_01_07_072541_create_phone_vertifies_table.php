<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    class CreatePhoneVertifiesTable extends Migration
    {
        /**
         * Run the migrations.
         *
         * @return void
         */
        public function up() {

            Schema::table('users', function (Blueprint $table) {
                $table->string('phone')->nullable()->comment("телефон без +7 для авторизации");
            });

            Schema::create('phone_vertifies', function(Blueprint $table) {
                $table->id();
                $table->integer('try_count')->default(0)->comment("Колв попыток");
                $table->integer('user_id')->default(0)->comment("пользователь к которому привязано");
                $table->string('code')->comment("код")->nullable();
                $table->string('ip')->comment("ипишник")->nullable();
                $table->string('phone')->comment("телефон")->nullable();
                $table->boolean('is_sended_on_phone')->comment("если получилось отправить на телефон смску. Типа вдруг апи не отработал")->default(false);
                $table->timestamp('last_try')->comment("посл попытка авторизации")->nullable();

                $table->timestamps();
            });
        }

        /**
         * Reverse the migrations.
         *
         * @return void
         */
        public function down() {
            Schema::dropIfExists('phone_vertifies');

            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn(['phone']);
            });
        }

    }
