<?php
// Preparação local. Não apaga dados, não redefine senhas existentes e não altera um .env existente.
if (PHP_SAPI !== 'cli') { exit('Execute este arquivo pelo terminal.'); }
chdir(__DIR__);
if (version_compare(PHP_VERSION, '8.3.0', '<')) { exit("Este projeto precisa de PHP 8.3 ou superior.\n"); }
foreach (['pdo_sqlite','mbstring','openssl','fileinfo','dom','xml','ctype','curl','tokenizer','session','filter'] as $extension) {
    if (!extension_loaded($extension)) { fwrite(STDERR,"Ative a extensão $extension no seu php.ini (localize com php --ini).\n"); exit(1); }
}
if (!is_file('vendor/autoload.php')) { fwrite(STDERR,"Execute composer install antes de continuar.\n"); exit(1); }
if (!is_file('.env')) { copy('.env.example','.env'); }
foreach (['storage/app/private','storage/app/public','storage/framework/cache/data','storage/framework/sessions','storage/framework/views','storage/logs','bootstrap/cache'] as $dir) {
    if (!is_dir($dir)) { mkdir($dir,0775,true); }
}
if (!is_file('database/database.sqlite')) { touch('database/database.sqlite'); }
require 'vendor/autoload.php';
$app=require 'bootstrap/app.php';
$kernel=$app->make(Illuminate\Contracts\Console\Kernel::class); $kernel->bootstrap();
if (!$app->environment('local')) { fwrite(STDERR,"Este instalador é exclusivo do ambiente local.\n"); exit(1); }
function runCommand($kernel, string $command, array $args=[]): void {
    $code=$kernel->call($command,$args); echo $kernel->output(); if ($code!==0) { exit($code); }
}
if (!config('app.key')) { runCommand($kernel,'key:generate'); }
runCommand($kernel,'migrate',['--force'=>true]);
runCommand($kernel,'db:seed',['--class'=>'Database\\Seeders\\DemoSeeder','--force'=>true]);
echo "\nCentral preparada! Execute: php artisan serve\nAbra http://127.0.0.1:8000\nDemo: admin@central.test | Senha: Central@123\n";
