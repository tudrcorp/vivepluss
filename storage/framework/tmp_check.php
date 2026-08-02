<?php
require __DIR__ . "/vendor/autoload.php";
$app = require __DIR__ . "/bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$coverages = App\Models\Coverage::where("plan_id", 2)->orderBy("price")->get(["id","price","plan_id"]);
echo "plan 2 coverages (vivepluss): " . $coverages->count() . PHP_EOL;
echo json_encode($coverages) . PHP_EOL;

$pivot = Illuminate\Support\Facades\DB::connection("mysql_vivepluss")->table("benefit_coverages")->whereIn("coverage_id", $coverages->pluck("id"))->count();
echo "pivot vivepluss for plan2 coverages: " . $pivot . PHP_EOL;

$pivotAll = Illuminate\Support\Facades\DB::connection("mysql_vivepluss")->table("benefit_coverages")->count();
echo "pivot vivepluss total: " . $pivotAll . PHP_EOL;

$benefits = App\Models\BenefitPlan::where("plan_id", 2)->count();
echo "benefits plan 2: " . $benefits . PHP_EOL;
