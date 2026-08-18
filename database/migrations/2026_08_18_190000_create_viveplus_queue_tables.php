<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tablas de cola propias de ViVEplus (mismo esquema que las de Laravel en
 * 0001_01_01_000002_create_jobs_table, pero en mysql_vivepluss).
 *
 * Hasta ahora la cola de ViVEplus vivía en `operaciones.jobs` -la conexión
 * por defecto- que es la MISMA tabla que usa Integracorp: sus dos workers
 * (ambos escuchando la cola `default`) se robaban los jobs entre sí y los
 * mataban, porque las clases de un proyecto no existen en el otro. Por eso
 * las notificaciones encoladas (comprobante de pago, documentos, kit de
 * bienvenida) se perdían de forma intermitente y sin error visible.
 *
 * Con DB_QUEUE_CONNECTION=mysql_vivepluss cada proyecto tiene su propia
 * tabla `jobs` en su propia base y los workers dejan de cruzarse, sin
 * necesidad de cambiar el nombre de la cola ni la unidad de systemd.
 */
return new class extends Migration
{
    protected $connection = 'mysql_vivepluss';

    public function up(): void
    {
        $schema = Schema::connection($this->connection);

        if (! $schema->hasTable('jobs')) {
            $schema->create('jobs', function (Blueprint $table) {
                $table->id();
                $table->string('queue')->index();
                $table->longText('payload');
                $table->unsignedTinyInteger('attempts');
                $table->unsignedInteger('reserved_at')->nullable();
                $table->unsignedInteger('available_at');
                $table->unsignedInteger('created_at');
            });
        }

        if (! $schema->hasTable('job_batches')) {
            $schema->create('job_batches', function (Blueprint $table) {
                $table->string('id')->primary();
                $table->string('name');
                $table->integer('total_jobs');
                $table->integer('pending_jobs');
                $table->integer('failed_jobs');
                $table->longText('failed_job_ids');
                $table->mediumText('options')->nullable();
                $table->integer('cancelled_at')->nullable();
                $table->integer('created_at');
                $table->integer('finished_at')->nullable();
            });
        }

        if (! $schema->hasTable('failed_jobs')) {
            $schema->create('failed_jobs', function (Blueprint $table) {
                $table->id();
                $table->string('uuid')->unique();
                $table->text('connection');
                $table->text('queue');
                $table->longText('payload');
                $table->longText('exception');
                $table->timestamp('failed_at')->useCurrent();
            });
        }
    }

    public function down(): void
    {
        $schema = Schema::connection($this->connection);

        $schema->dropIfExists('jobs');
        $schema->dropIfExists('job_batches');
        $schema->dropIfExists('failed_jobs');
    }
};
