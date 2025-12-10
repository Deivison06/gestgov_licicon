<?php

namespace App\Helpers;

class ValorPorExtenso
{
    private static $unidades = [
        "", "um", "dois", "três", "quatro", "cinco", "seis", "sete", "oito", "nove",
        "dez", "onze", "doze", "treze", "quatorze", "quinze", "dezesseis", "dezessete",
        "dezoito", "dezenove"
    ];

    private static $dezenas = [
        "", "", "vinte", "trinta", "quarenta", "cinquenta",
        "sessenta", "setenta", "oitenta", "noventa"
    ];

    private static $centenas = [
        "", "cento", "duzentos", "trezentos", "quatrocentos", "quinhentos",
        "seiscentos", "setecentos", "oitocentos", "novecentos"
    ];

    private static $milhares = [
        ["", ""],
        ["mil", "mil"],
        ["milhão", "milhões"],
        ["bilhão", "bilhões"],
        ["trilhão", "trilhões"]
    ];

    public static function escrever($valor, $moeda = 'real', $centavoMoeda = 'centavo')
    {
        // Validação básica
        if (!is_numeric($valor)) {
            return "Valor inválido";
        }

        // Arredonda para 2 casas decimais
        $valor = round($valor, 2);
        
        // Separa parte inteira e decimal
        $partes = explode('.', number_format($valor, 2, '.', ''));
        $inteiro = (int)$partes[0];
        $centavos = isset($partes[1]) ? (int)$partes[1] : 0;

        // Trata o valor zero
        if ($inteiro == 0 && $centavos == 0) {
            return "zero {$moeda}" . ($moeda != 'real' ? 's' : '');
        }

        // Escreve a parte inteira
        $textoInteiro = self::escreverNumero($inteiro);
        
        // Pluraliza a moeda
        $moedaTexto = ($inteiro == 1) ? $moeda : ($moeda == 'real' ? 'reais' : $moeda . 's');

        // Escreve a parte decimal
        $textoCentavos = '';
        if ($centavos > 0) {
            $textoCentavos = self::escreverNumero($centavos);
            $centavoTexto = ($centavos == 1) ? $centavoMoeda : $centavoMoeda . 's';
            $textoCentavos .= " {$centavoTexto}";
        }

        // Monta o texto final
        $texto = $textoInteiro . " {$moedaTexto}";
        if ($centavos > 0) {
            $texto .= " e {$textoCentavos}";
        }

        return trim($texto);
    }

    private static function escreverNumero($numero)
    {
        if ($numero == 0) {
            return "";
        }

        if ($numero < 20) {
            return self::$unidades[$numero];
        }

        if ($numero < 100) {
            $dezena = floor($numero / 10);
            $unidade = $numero % 10;
            
            $texto = self::$dezenas[$dezena];
            if ($unidade > 0) {
                $texto .= " e " . self::$unidades[$unidade];
            }
            return $texto;
        }

        if ($numero < 1000) {
            $centena = floor($numero / 100);
            $resto = $numero % 100;
            
            $texto = "";
            if ($numero == 100) {
                $texto = "cem";
            } else {
                $texto = self::$centenas[$centena];
            }
            
            if ($resto > 0) {
                $texto .= " e " . self::escreverNumero($resto);
            }
            return $texto;
        }

        // Para números maiores
        $grupos = [];
        while ($numero > 0) {
            $grupos[] = $numero % 1000;
            $numero = floor($numero / 1000);
        }

        $texto = "";
        for ($i = count($grupos) - 1; $i >= 0; $i--) {
            if ($grupos[$i] == 0) {
                continue;
            }

            $grupoTexto = self::escreverNumero($grupos[$i]);
            
            if ($i > 0) {
                $singularPlural = ($grupos[$i] == 1) ? self::$milhares[$i][0] : self::$milhares[$i][1];
                $grupoTexto .= " " . $singularPlural;
            }

            if ($texto != "") {
                $texto .= ", ";
            }
            $texto .= $grupoTexto;
        }

        return $texto;
    }
}