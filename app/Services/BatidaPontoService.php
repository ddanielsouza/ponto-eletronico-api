<?php
namespace App\Services;
use App\Models\BatidaPonto;

class BatidaPontoService{
    public function calcularHorasTrabalhadas($batidas, $dataInicio, $dataFim){
        $batidasDias = [];
        foreach($batidas as $batida){
            if(array_key_exists($batida->horaBatida->format('Y-m-d'), $batidasDias)){
                $batidasDias[$batida->horaBatida->format('Y-m-d')][] = $batida;
            }
            else{
                $batidasDias[$batida->horaBatida->format('Y-m-d')] = [$batida];
            }
        }

        $results = [];
        for($dataInicio; $dataInicio <= $dataFim; $dataInicio->modify('+1 day')){
            $date = $dataInicio->format('Y-m-d');

            if(array_key_exists($date, $batidasDias)){
                $batidasDia = $batidasDias[$date ];
                $calc = $this->calculeTempoTrabalhadosDia($batidasDia);
                $results[] = [
                    'data' => $date,
                    'horaTrabalhadas' => $calc['horasTrabalhadas'],
                    'totalSeconds' => $calc['totalSeconds'],
                    'pontosBatidos' => array_map(function ($pb){return $pb->horaBatida->toIsoString();}, $batidasDia)
                ];
            }
            else{
                $results[] = [
                    'data' => $date,
                    'horaTrabalhadas' => '00:00:00',
                    'totalSeconds' => 0,
                    'pontosBatidos' => []
                ];
            }
        }

        return $results;
    }

    public function calculeTempoTrabalhadosDia($batidas){
        //Class DateTime permite comparações
        usort($batidas, function ($item1, $item2) {
            if($item1->horaBatida == $item2->horaBatida){
                return 0;
            }
            return $item1->horaBatida < $item2->horaBatida ? -1 : 1;
        });

        $totalSeconds = 0;
        for($i = 0; $i < count($batidas); $i++){
            if($i % 2 === 0 && array_key_exists($i + 1, $batidas)){
                $hora1 = $batidas[$i]->horaBatida;
                $hora2 = $batidas[$i + 1]->horaBatida;
                
                $totalSeconds += ($hora2->getTimestamp() - $hora1->getTimestamp());
            }
        }
        $seconds = $totalSeconds;

        $hours = 0;
        if ( $seconds > 3600 ){
            $hours = floor( $seconds / 3600 );
        }
        $seconds = $seconds % 3600;
        $horasTrabalhadas = str_pad( $hours, 2, '0', STR_PAD_LEFT ). gmdate( ':i:s', $seconds );

        return ['horasTrabalhadas' => $horasTrabalhadas, 'totalSeconds' => $totalSeconds];
    }
}