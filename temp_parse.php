<?php
require __DIR__ . '/vendor/autoload.php';
use App\Services\ChatbotAppointmentService;
use Carbon\Carbon;
Carbon::setTestNow('2026-07-29 08:00:00');
$service = new ChatbotAppointmentService();
$ref = new ReflectionClass($service);
$method = $ref->getMethod('parseDate');
$method->setAccessible(true);
$tests = ['04/07/2026', '04/07', '4 de julio', '04-07', '04/07/26'];
foreach ($tests as $text) {
    $date = $method->invoke($service, $text);
    echo $text . ' => ' . ($date ? $date->format('Y-m-d') : 'null') . PHP_EOL;
}
