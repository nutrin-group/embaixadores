<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CommissionService;

class ProcessCommissions extends Command
{
    protected $signature = 'commissions:process';
    protected $description = 'Process commissions and release locked amounts';

    public function handle(CommissionService $service)
    {
        $this->info('Processando comissões...');
        $service->processReleasableCommissions();
        $this->info('Processamento concluído!');
    }
}
