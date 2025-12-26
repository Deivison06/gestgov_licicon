<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Contrato - Processo {{ $processo->numero_processo ?? $processo->id }}</title>
    <style type="text/css">
        @font-face {
            font-family: 'Aptos';
            src: url('{{ public_path('storage/fonts/Aptos.ttf') }}') format('truetype');
            font-style: normal;
        }

        @font-face {
            font-family: 'AptosExtraBold';
            src: url('{{ public_path('storage/fonts/Aptos-ExtraBold.ttf') }}') format('truetype');
            font-style: normal;
        }


        @page {
            margin: 0;
            size: A4;
        }

        body {
            margin: 0;
            padding: 4cm 2cm;
            font-size: 11pt;
            font-family: 'Aptos', sans-serif;
            /* Adiciona o timbre como background */
            background-image: url('{{ public_path($prefeitura->timbre) }}');
            background-repeat: no-repeat;
            background-position: top left;
            background-size: cover;

            text-align: justify;
            text-justify: inter-word;
            line-height: 1;
        }

        /* CLASSE PARA FORÇAR QUEBRA DE PÁGINA (ESSENCIAL PARA PDF) */
        .page-break {
            page-break-after: always;
        }

        /* ---------------------------------- */
        /* ESTILOS - CAPA DO DOCUMENTO (PÁGINA 0) */
        /* ---------------------------------- */
        #cover-page {
            /* Define a área de referência como a página inteira */
            height: 100vh;
            width: 100%;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
        }

        .cover-image {
            /* Tamanho da imagem */
            width: 300px;
            height: 300px;
            margin-bottom: 30px;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }

        .cover-title {
            width: 60%;
            font-size: 18pt;
            font-weight: 900;
            border: 2px solid #000;
            display: inline-block;
            line-height: 0.9;
            padding: 10px 50px;
            font-family: 'AptosExtraBold', sans-serif;
        }

        .footer-signature {
            margin-top: 60px;
            text-align: right;
        }

        .signature-block {
            margin-top: 60px;
            text-align: center;
        }

        /* Estilos opcionais para simular as linhas da imagem */
        .line {
            border-top: 2px solid black;
            margin: 10px 0;
            /* Espaçamento entre as linhas */
        }

        .content {
            /* Centraliza o texto como na imagem */
            margin: 40px 0;
            /* Espaçamento acima e abaixo do conteúdo principal */
        }

        strong {
            line-height: 1.5;
            /* Melhora a leitura do texto em várias linhas */
            display: block;
            /* Garante que o strong ocupe a largura total */
        }

                /* ---------------------------------- */
        /* ESTILOS - CONTEÚDO PRINCIPAL */
        /* ---------------------------------- */
        .container {
            position: relative;
            width: 100%;
            height: 100%;
        }

        .conteudo-all {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 100%;
            transform: translate(-50%, -50%);
            text-align: left;
        }

        /* .conteudo {
            margin: 0 90px;
        } */

        .title {
            text-align: center;
            margin-left: -85px;
            font-weight: bold;
            font-size: 20pt;
            background: #bebebe;
            border: 1px solid #7a7a7a;
            padding: 5px 10px;
            display: inline-block;
            margin-bottom: 20px;
        }

        .section {
            margin-bottom: 15px;
        }

    </style>
</head>

<body>
    {{-- ====================================================================== --}}
    {{-- BLOCO 1: CAPA DO DOCUMENTO --}}
    {{-- ====================================================================== --}}
    <div id="cover-page">
        <img src="{{ public_path('icons/capa-documento.png') }}" alt="Martelo da Justiça" class="cover-image">
        <div class="cover-title">
            CONTRATO E EXTRATO CONTRATO
        </div>
    </div>

    {{-- QUEBRA DE PÁGINA --}}
    <div class="page-break"></div>

    <div>
        <h4>
            PROCESSO ADMINISTRATIVO Nº {{ $processo->numero_processo }} <br>
            PREGÃO ELETRÔNICO Nº {{ $processo->numero_procedimento }}
        </h4>

        <table style="width:100%; table-layout:fixed; border-collapse:collapse;">
            <tr>
                <td style="width:40%; padding:8px; vertical-align:top; word-wrap:break-word; white-space:normal;">
                <!-- Conteúdo da primeira célula -->
                </td>
                <td style="width:60%; padding:8px; vertical-align:top; word-wrap:break-word; white-space:normal;">
                    PREGÃO ELETRÔNICO Nº {{ $processo->numero_procedimento }}, QUE FAZEM ENTRE SI A
                    {{ $processo->finalizacao->orgao_responsavel }} E {{ $dadosContratado['razao_social'] }}
                </td>
            </tr>
        </table>

        <div class="section">
            <table>
                <tr>
                    <td class="icon">
                        <img src="{{ public_path('icons/Imagem1.png') }}" width="40">
                    </td>
                    <td class="content">
                        <div style=" font-weight: bold; margin-bottom: 3px;">Contratante</div>
                        <div style="">
                            {{ $processo->finalizacao->orgao_responsavel }}, com sede no(a) {{ $processo->prefeitura->endereco }}, na cidade de {{ $processo->prefeitura->cidade }} inscrito(a) no CNPJ
                            sob o nº {{ $processo->finalizacao->cnpj }}, neste ato representado(a) pelo(a) {{ $processo->finalizacao->responsavel }} inscrito no CPF sob n° {{ $processo->finalizacao->cpf_responsavel }}.
                        </div>
                    </td>
                </tr>
            </table>
        </div>
        <div class="section">
            <table>
                <tr>
                    <td class="icon">
                        <img src="{{ public_path('icons/Imagem1.png') }}" width="40">
                    </td>
                    <td class="content">
                        <div style=" font-weight: bold; margin-bottom: 3px;">Contratado</div>
                        <div style="">
                            {{ $dadosContratado['razao_social'] }},  inscrito(a) no CNPJ/MF sob o nº {{ $dadosContratado['cnpj_formatado'] }}, sediado(a) na {{ $dadosContratado['endereco'] }} neste
                            ato representado(a) por {{ $dadosContratado['representante'] }}, inscrito no CPF sob n° {{ $dadosContratado['cpf_representante_formatado'] }}.
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <div style="margin-bottom: 20px;">
            <img src="{{ public_path('icons/descricao-necessidade.png') }}" width="30px"
            alt="DESCRIÇÃO DA NECESSIDADE">
            <h4 style="display: inline-block; margin: 0 0 0 10px; vertical-align: middle;">
                CLÁUSULA PRIMEIRA – DO OBJETO</h4>
        </div>

        <p style="text-align: justify;">
            1.1 {!! strip_tags($processo->objeto) !!}
        </p>

        <table style="width:100%; border-collapse:collapse; font-size:8pt;">
            <thead>
                <tr>
                    <th style="border:2px solid #000; padding:8px; text-align:left; font-weight:700; width:6%;">ITEM</th>
                    <th style="border:2px solid #000; padding:8px; text-align:left; font-weight:700; width:46%;">ESPECIFICAÇÃO</th>
                    <th style="border:2px solid #000; padding:8px; text-align:center; font-weight:700; width:16%;">UNIDADE<br>DE MEDIDA</th>
                    <th style="border:2px solid #000; padding:8px; text-align:center; font-weight:700; width:10%;">QUANTIDADE</th>
                    <th style="border:2px solid #000; padding:8px; text-align:center; font-weight:700; width:11%;">VALOR<br>UNITÁRIO</th>
                    <th style="border:2px solid #000; padding:8px; text-align:center; font-weight:700; width:11%;">VALOR<br>TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @if(count($itensTabela) > 0)
                    @foreach($itensTabela as $item)
                        <tr>
                            <td style="border:1px solid #000; padding:14px; vertical-align:top; text-align:center;">{{ $item['item'] }}</td>
                            <td style="border:1px solid #000; padding:14px; vertical-align:top;">{{ $item['especificacao'] }}</td>
                            <td style="border:1px solid #000; padding:14px; vertical-align:top; text-align:center;">{{ $item['unidade_medida'] }}</td>
                            <td style="border:1px solid #000; padding:14px; vertical-align:top; text-align:center;">{{ $item['quantidade'] }}</td>
                            <td style="border:1px solid #000; padding:14px; vertical-align:top; text-align:right;">{{ $item['valor_unitario'] }}</td>
                            <td style="border:1px solid #000; padding:14px; vertical-align:top; text-align:right;">{{ $item['valor_total'] }}</td>
                        </tr>
                    @endforeach
                    
                    <!-- Linha de totalização -->
                    <tr>
                        <td colspan="3" style="border:1px solid #000; padding:14px; vertical-align:top; text-align:right; font-weight:bold;">TOTAL GERAL</td>
                        <td colspan="3" style="border:1px solid #000; padding:14px; vertical-align:top; text-align:right; font-weight:bold;">R$ {{ number_format($valorTotalContrato, 2, ',', '.') }}</td>
                    </tr>
                @else
                    <tr>
                        <td colspan="6" style="border:1px solid #000; padding:14px; text-align:center; color:red;">
                            Nenhum item contratado encontrado.
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>

        <p style="text-align: justify;">
            1.1. Vinculam esta contratação, independentemente de transcrição:
        </p>
        <p style="text-align: justify;">
            1.1.1. O Termo de Referência;
        </p>
        <p style="text-align: justify;">
            1.1.2. O Edital da Licitação;
        </p>
        <p style="text-align: justify;">
            1.1.3. A Proposta do contratado;
        </p>
        <p style="text-align: justify;">
            1.1.4. Eventuais anexos dos documentos supracitados.
        </p>

        <div style="margin-bottom: 20px;">
            <img src="{{ public_path('icons/descricao-necessidade.png') }}" width="30px"
            alt="DESCRIÇÃO DA NECESSIDADE">
            <h4 style="display: inline-block; margin: 0 0 0 10px; vertical-align: middle;">
                CLÁUSULA SEGUNDA – CLÁUSULA SEGUNDA – VIGÊNCIA E PRORROGAÇÃO </h4>
        </div>
        @php
            $vigencia = is_array($processo->detalhe->prazo_vigencia ?? null)
                ? $processo->detalhe->prazo_vigencia
                : ['12_meses'];

            $outro_vigencia = $processo->detalhe->prazo_vigencia_outro ?? '________________.';

            $objeto_continuado = strtolower($processo->detalhe->objeto_continuado ?? 'nao');

            // Texto para preencher automaticamente
            if (in_array('exercicio_financeiro', $vigencia)) {
                $textoVigencia = "até 31/12 do exercício financeiro da contratação";
            } elseif (in_array('12_meses', $vigencia)) {
                $textoVigencia = "12 meses";
            } elseif (in_array('outro', $vigencia)) {
                $textoVigencia = $outro_vigencia;
            } else {
                $textoVigencia = "________________";
            }
        @endphp
        <p style="text-align: justify;">
            2.1. 9O prazo de vigência da contratação é de <span style="font-weight:bold; text-decoration:underline;"> {{ $textoVigencia }} </span>, 
            contados contados da ordem de Serviços, prorrogável na forma dos artigos 106 e 107 da Lei n° 14.133, de 2021. <br><br>
            2.2. A prorrogação de que trata este item é condicionada ao ateste, pela autoridade
            competente, de que as condições e os preços permanecem vantajosos para a
            Administração, permitida a negociação com o contratado.
        </p>

        <div style="margin-bottom: 20px;">
            <img src="{{ public_path('icons/descricao-necessidade.png') }}" width="30px"
            alt="DESCRIÇÃO DA NECESSIDADE">
            <h4 style="display: inline-block; margin: 0 0 0 10px; vertical-align: middle;">
                CLÁUSULA TERCEIRA – MODELOS DE EXECUÇÃO E GESTÃO CONTRATUAIS</h4>
        </div>

        <p style="text-align: justify;">
            3.1. O regime de execução contratual, os modelos de gestão e de execução, assim
            como os prazos e condições de conclusão, entrega, observação e recebimento do objeto
            constam no Termo de Referência, anexo a este Contrato.
        </p>

        <div style="margin-bottom: 20px;">
            <img src="{{ public_path('icons/descricao-necessidade.png') }}" width="30px"
            alt="DESCRIÇÃO DA NECESSIDADE">
            <h4 style="display: inline-block; margin: 0 0 0 10px; vertical-align: middle;">
                CLÁUSULA QUARTA – SUBCONTRATAÇÃO</h4>
        </div>

        @if ($processo->contrato->subcontratacao === 0)
            <p style="text-align: justify;">
                4.1. Não será admitida a subcontratação do objeto contratual.
            </p>
        @else
            <p style="text-align: justify;">
                4.2. É permitida a subcontratação parcial do objeto, até o limite de 50% (cinquenta por cento)
                do valor total do contrato, nas seguintes condições:<br><br>
                4.2.1. Em qualquer hipótese de subcontratação, permanece a responsabilidade integral
                do contratado pela perfeita execução contratual, cabendo-lhe realizar a supervisão e
                coordenação das atividades do subcontratado, bem como responder perante o
                contratante pelo rigoroso cumprimento das obrigações contratuais correspondentes ao
                objeto da subcontratação.
                <br><br>
                4.3. A subcontratação depende de autorização prévia do contratante, a quem incumbe
                avaliar se o subcontratado cumpre os requisitos de qualificação técnica necessários para
                a execução do objeto.
                <br><br>
                4.3.1. O contratado apresentará à Administração documentação que comprove a
                capacidade técnica do subcontratado, que será avaliada e juntada aos autos do processo
                correspondente.
                <br><br>
                4.4. É vedada a subcontratação de pessoa física ou jurídica, se aquela ou os dirigentes
                desta mantiverem vínculo de natureza técnica, comercial, econômica, financeira,
                trabalhista ou civil com dirigente do órgão ou entidade contratante ou com agente público
                que desempenhe função na contratação ou atue na fiscalização ou na gestão do contrato,
                ou se deles forem cônjuge, companheiro ou parente em linha reta, colateral, ou por
                afinidade, até o terceiro grau.
            </p>
        @endif

        <div style="margin-bottom: 20px;">
            <img src="{{ public_path('icons/descricao-necessidade.png') }}" width="30px"
            alt="DESCRIÇÃO DA NECESSIDADE">
            <h4 style="display: inline-block; margin: 0 0 0 10px; vertical-align: middle;">
                CLÁUSULA QUINTA - PREÇO</h4>
        </div>

        <p style="text-align: justify;">
            5.1. O valor total da contratação é de R$ {{ number_format($valorTotalContrato, 2, ',', '.') }} ({{ $valorTotalPorExtenso }}).
            <br><br>
            5.2. No valor acima estão incluídas todas as despesas ordinárias diretas e indiretas
            decorrentes da execução do objeto, inclusive tributos e/ou impostos, encargos sociais,
            trabalhistas, previdenciários, fiscais e comerciais incidentes, taxa de administração, frete,
            seguro e outros necessários ao cumprimento integral do objeto da contratação.
            <br><br>
            @if ($processo->tipo_procedimento === 'SERVIÇOS')
                5.3. O valor acima é meramente estimativo, de forma que os pagamentos devidos ao contratado dependerão dos quantitativos efetivamente prestados
            @else
                5.3. O valor acima é meramente estimativo, de forma que os pagamentos devidos ao
                contratado dependerão dos quantitativos efetivamente fornecidos.
            @endif
        </p>

        <div style="margin-bottom: 20px;">
            <img src="{{ public_path('icons/descricao-necessidade.png') }}" width="30px"
            alt="DESCRIÇÃO DA NECESSIDADE">
            <h4 style="display: inline-block; margin: 0 0 0 10px; vertical-align: middle;">
                CLÁUSULA SEXTA - PAGAMENTO</h4>
        </div>

        <p style="text-align: justify;">
            6.1. Recebida a Nota Fiscal ou documento de cobrança equivalente, correrá o prazo de trinta dias
            para fins de liquidação, na forma
            <br><br>
            6.2. O pagamento do(s) produto(s) será(ão) efetuado(s) pela CONTRATANTE, mediante a emissão
            de nota fiscal e recibo por parte da CONTRATADA com o visto do funcionário responsável pela
            fiscalização dos serviços.
        </p>

        <div style="margin-bottom: 20px;">
            <img src="{{ public_path('icons/descricao-necessidade.png') }}" width="30px"
            alt="DESCRIÇÃO DA NECESSIDADE">
            <h4 style="display: inline-block; margin: 0 0 0 10px; vertical-align: middle;">
                CLÁUSULA SÉTIMA - REAJUSTE</h4>
        </div>

        <p style="text-align: justify;">
            7.1. Os preços inicialmente contratados são fixos e irreajustáveis no prazo de um ano
            contado da data do orçamento estimado.
            <br><br>
            7.2. Após o interregno de um ano, e independentemente de pedido do contratado, os
            preços iniciais serão reajustados, mediante a aplicação, pelo contratante, do índice
            Inflacionário, exclusivamente para as obrigações iniciadas e concluídas após a ocorrência
            da anualidade.
            <br><br>
            7.3. Nos reajustes subsequentes ao primeiro, o interregno mínimo de um ano será
            contado a partir dos efeitos financeiros do último reajuste.
            <br><br>
            7.4. No caso de atraso ou não divulgação do(s) índice (s) de reajustamento, o
            contratante pagará ao contratado a importância calculada pela última variação conhecida,
            liquidando a diferença correspondente tão logo seja(m) divulgado(s) o(s) índice(s)
            definitivo(s).
            <br><br>
            7.5. Nas aferições finais, o(s) índice(s) utilizado(s) para reajuste será(ão),
            obrigatoriamente, o(s) definitivo(s).
            <br><br>
            7.6. Caso o(s) índice(s) estabelecido(s) para reajustamento venha(m) a ser extinto(s) ou
            de qualquer forma não possa(m) mais ser utilizado(s), será(ão) adotado(s), em
            substituição, o(s) que vier(em) a ser determinado(s) pela legislação então em vigor.
            <br><br>
            7.7. Na ausência de previsão legal quanto ao índice substituto, as partes elegerão novo
            índice oficial, para reajustamento do preço do valor remanescente, por meio de termo
            aditivo.
            <br><br>
            7.8. O reajuste será realizado por apostilamento.
        </p>

        <div style="margin-bottom: 20px;">
            <img src="{{ public_path('icons/descricao-necessidade.png') }}" width="30px"
            alt="DESCRIÇÃO DA NECESSIDADE">
            <h4 style="display: inline-block; margin: 0 0 0 10px; vertical-align: middle;">
                CLÁUSULA OITAVA - OBRIGAÇÕES DO CONTRATANTE</h4>
        </div>

        <p style="text-align: justify;">
            8.1. São obrigações do Contratante:
            <br><br>
            8.2. Exigir o cumprimento de todas as obrigações assumidas pelo Contratado, de
            acordo com o contrato e seus anexos;
            <br><br>
            8.3. Receber o objeto no prazo e condições estabelecidas no Termo de Referência;
            <br><br>
            @if ($processo->tipo_procedimento === 'SERVIÇOS')
                8.4. Notificar o Contratado, por escrito, sobre vícios, defeitos ou incorreções verificadas 
                nos serviços prestados, para que seja por ele substituído, reparado ou corrigido, no total 
                ou em parte, às suas expensas;
            @else
                8.4. Notificar o Contratado, por escrito, sobre vícios, defeitos ou incorreções verificadas
                no objeto fornecido, para que seja por ele substituído, reparado ou corrigido, no total ou
                em parte, às suas expensas;
            @endif
            
            <br><br>
            8.5. Acompanhar e fiscalizar a execução do contrato e o cumprimento das obrigações
            pelo Contratado;
            <br><br>
            8.6. Comunicar a empresa para emissão de Nota Fiscal no que pertine à parcela
            incontroversa da execução do objeto, para efeito de liquidação e pagamento, quando
            houver controvérsia sobre a execução do objeto, quanto à dimensão, qualidade e
            quantidade, conforme o art. 143 da Lei nº 14.133, de 2021;
            <br><br>
            @if ($processo->tipo_procedimento === 'SERVIÇOS')
                8.7. Efetuar o pagamento ao Contratado do valor correspondente a prestação do serviço, 
                no prazo, forma e condições estabelecidos no presente Contrato;
            @else
                8.7. Efetuar o pagamento ao Contratado do valor correspondente ao fornecimento do
                objeto, no prazo, forma e condições estabelecidos no presente Contrato;
            @endif
            <br><br>
            8.8. Aplicar ao Contratado as sanções previstas na lei e neste Contrato;
            <br><br>
            8.9. Cientificar o órgão de representação judicial da Advocacia-Geral da União para
            adoção das medidas cabíveis quando do descumprimento de obrigações pelo Contratado;
            <br><br>
            8.10. Explicitamente emitir decisão sobre todas as solicitações e reclamações
            relacionadas à execução do presente Contrato, ressalvados os requerimentos
            manifestamente impertinentes, meramente protelatórios ou de nenhum interesse para a
            boa execução do ajuste.
            <br><br>
            8.10.1. A Administração terá o prazo de 05 (cinco) dias, a contar da data do protocolo do
            requerimento para decidir, admitida a prorrogação motivada, por igual período.
            <br><br>
            8.11. Responder eventuais pedidos de reestabelecimento do equilíbrio econômicofinanceiro feitos pelo contratado no prazo máximo de 30 (trinta) dias.
            <br><br>
            8.12. A Administração não responderá por quaisquer compromissos assumidos pelo
            Contratado com terceiros, ainda que vinculados à execução do contrato, bem como por
            qualquer dano causado a terceiros em decorrência de ato do Contratado, de seus
            empregados, prepostos ou subordinados.

        </p>

        <div style="margin-bottom: 20px;">
            <img src="{{ public_path('icons/descricao-necessidade.png') }}" width="30px"
            alt="DESCRIÇÃO DA NECESSIDADE">
            <h4 style="display: inline-block; margin: 0 0 0 10px; vertical-align: middle;">
                CLÁUSULA NONA - OBRIGAÇÕES DO CONTRATADO</h4>
        </div>

        <p style="text-align: justify;">
            9.1. O Contratado deve cumprir todas as obrigações constantes deste Contrato e em
            seus anexos, assumindo como exclusivamente seus os riscos e as despesas decorrentes
            da boa e perfeita execução do objeto, observando, ainda, as obrigações a seguir dispostas:
            <br><br>
            9.2. Responsabilizar-se pelos vícios e danos decorrentes do objeto, de acordo com o
            Código de Defesa do Consumidor (Lei nº 8.078, de 1990);
            <br><br>
            9.3. Comunicar ao contratante, no prazo máximo de 24 (vinte e quatro) horas que
            antecede a data da entrega, os motivos que impossibilitem o cumprimento do prazo
            previsto, com a devida comprovação;
            <br><br>
            9.4. Atender às determinações regulares emitidas pelo fiscal ou gestor do contrato ou
            autoridade superior (art. 137, II, da Lei n.º 14.133, de 2021) e prestar todo esclarecimento
            ou informação por eles solicitados;
            <br><br>
            9.5. Reparar, corrigir, remover, reconstruir ou substituir, às suas expensas, no total ou
            em parte, no prazo fixado pelo fiscal do contrato, os bens nos quais se verificarem vícios,
            defeitos ou incorreções resultantes da execução ou dos materiais empregados;
            <br><br>
            9.6. Responsabilizar-se pelos vícios e danos decorrentes da execução do objeto, bem
            como por todo e qualquer dano causado à Administração ou terceiros, não reduzindo essa
            responsabilidade a fiscalização ou o acompanhamento da execução contratual pelo
            contratante, que ficará autorizado a descontar dos pagamentos devidos ou da garantia,
            caso exigida, o valor correspondente aos danos sofridos;
            <br><br>
            9.7. Quando não for possível a verificação da regularidade no Sistema de Cadastro de
            Fornecedores – SICAF, o contratado deverá entregar ao setor responsável pela fiscalização
            do contrato, junto com a Nota Fiscal para fins de pagamento, os seguintes documentos: 1)
            prova de regularidade relativa à Seguridade Social; 2) certidão conjunta relativa aos tributos
            federais e à Dívida Ativa da União; 3) certidões que comprovem a regularidade perante a
            Fazenda Estadual ou Distrital do domicílio ou sede do contratado; 4) Certidão de
            Regularidade do FGTS – CRF; e 5) Certidão Negativa de Débitos Trabalhistas – CNDT;
            <br><br>
            9.8. Responsabilizar-se pelo cumprimento de todas as obrigações trabalhistas,
            previdenciárias, fiscais, comerciais e as demais previstas em legislação específica, cuja
            inadimplência não transfere a responsabilidade ao contratante e não poderá onerar o
            objeto do contrato;
            <br><br>
            9.9. Comunicar ao Fiscal do contrato, no prazo de 24 (vinte e quatro) horas, qualquer
            ocorrência anormal ou acidente que se verifique no local da execução do objeto contratual.
            <br><br>
            9.10. Paralisar, por determinação do contratante, qualquer atividade que não esteja
            sendo executada de acordo com a boa técnica ou que ponha em risco a segurança de
            pessoas ou bens de terceiros.
            <br><br>
            9.11. Manter durante toda a vigência do contrato, em compatibilidade com as obrigações
            assumidas, todas as condições exigidas para habilitação na licitação;
            <br><br>
            9.12. Cumprir, durante todo o período de execução do contrato, a reserva de cargos
            prevista em lei para pessoa com deficiência, para reabilitado da Previdência Social ou para
            aprendiz, bem como as reservas de cargos previstas na legislação (art. 116, da Lei n.º
            14.133, de 2021);
            <br><br>
            9.13. Comprovar a reserva de cargos a que se refere a cláusula acima, no prazo fixado
            pelo fiscal do contrato, com a indicação dos empregados que preencheram as referidas
            vagas (art. 116, parágrafo único, da Lei n.º 14.133, de 2021);
            <br><br>
            9.14. Guardar sigilo sobre todas as informações obtidas em decorrência do
            cumprimento do contrato;
            <br><br>
            9.15. Arcar com o ônus decorrente de eventual equívoco no dimensionamento dos
            quantitativos de sua proposta, inclusive quanto aos custos variáveis decorrentes de fatores
            futuros e incertos, devendo complementá-los, caso o previsto inicialmente em sua
            proposta não seja satisfatório para o atendimento do objeto da contratação, exceto
            quando ocorrer algum dos eventos arrolados no art. 124, II, d, da Lei nº 14.133, de 2021.
            <br><br>
            9.16. Cumprir, além dos postulados legais vigentes de âmbito federal, estadual ou
            municipal, as normas de segurança do contratante;
            <br><br>
            9.17. Alocar os empregados necessários, com habilitação e conhecimento adequados,
            ao perfeito cumprimento das cláusulas deste contrato, fornecendo os materiais,
            equipamentos, ferramentas e utensílios demandados, cuja quantidade, qualidade e
            tecnologia deverão atender às recomendações de boa técnica e a legislação de regência;
            <br><br>
            9.18. Orientar e treinar seus empregados sobre os deveres previstos na Lei nº 13.709, de
            14 de agosto de 2018, adotando medidas eficazes para proteção de dados pessoais a que
            tenha acesso por força da execução deste contrato;
            <br><br>
            9.19. Conduzir os trabalhos com estrita observância às normas da legislação pertinente,
            cumprindo as determinações dos Poderes Públicos, mantendo sempre limpo o local de
            execução do objeto e nas melhores condições de segurança, higiene e disciplina.
            <br><br>
            9.20. Submeter previamente, por escrito, ao contratante, para análise e aprovação,
            quaisquer mudanças nos métodos executivos que fujam às especificações do memorial
            descritivo ou instrumento congênere.
            <br><br>
            9.21. Não permitir a utilização de qualquer trabalho do menor de dezesseis anos, exceto
            na condição de aprendiz para os maiores de quatorze anos, nem permitir a utilização do
            trabalho do menor de dezoito anos em trabalho noturno, perigoso ou insalubre.
            <br><br>
            9.22. As partes cooperarão entre si no cumprimento das obrigações referentes ao
            exercício dos direitos dos Titulares previstos na LGPD e nas Leis e Regulamentos de
            Proteção de Dados em vigor e também no atendimento de requisições e determinações do
            Poder Judiciário, Ministério Público, Órgãos de controle administrativo..
            <br><br>
            9.23. As partes responderão administrativa e judicialmente, em caso de causarem danos
            patrimoniais, morais, individual ou coletivo, aos titulares de dados pessoais, repassados
            em decorrência da execução contratual, por inobservância à LGPD.
            <br><br>
            9.24. Em atendimento ao disposto na Lei n. 13.709/2018 - Lei Geral de Proteção de Dados
            Pessoais (LGPD), a CONTRATANTE, para a execução do serviço objeto deste contrato, terá
            acesso a dados pessoais dos representantes da CONTRATADA, tais como: número do CPF
            e do RG, endereço eletrônico, e cópia do documento de identificação.
            <br><br>
            9.25. A critério do Encarregado de Dados da CONTRATANTE, a CONTRATADA poderá ser
            provocada a colaborar na elaboração do relatório de impacto à proteção de dados
            pessoais (RIPD), conforme a sensibilidade e o risco inerente dos serviços objeto deste
            contrato, no tocante a dados pessoais.
            <br><br>
            9.26. A CONTRATADA fica obrigada a comunicar ao CONTRATANTE, em até 24 (vinte e
            quatro) horas, qualquer incidente de acessos não autorizados aos dados pessoais,
            situações acidentais ou ilícitas de destruição, perda, alteração, comunicação ou qualquer
            forma de tratamento inadequado ou ilícito, bem como adotar as providências dispostas no
            art. 48 da LGPD.
            <br><br>
            9.27. Encerrada a vigência do contrato ou não havendo mais necessidade de utilização
            dos dados pessoais, sensíveis ou não, a CONTRATADA interromperá o tratamento e, em no
            máximo 30 (trinta) dias, sob instruções e na medida do determinado pela CONTRATANTE,
            eliminará completamente os Dados Pessoais e todas as cópias porventura existentes (em
            formato digital, físico ou outro qualquer), salvo quando necessite mantê-los para
            cumprimento de obrigação legal ou outra hipótese legal prevista na LGPD.

            @if ($processo->finalizacao->merenda_escolar === "Sim")
                9.28. O prazo do fornecimento será imediatamente após apresentação da ordem de
                fornecimento, em conformidade com o este Termo de Referência e a Emissão da ORDEM
                DE FORNECIMENTO emitida pelo órgão demandante.
                <br><br>
                9.29. O fornecimento ocorrerá conforme a necessidade da Secretaria Municipal de
                Educação;
                <br><br>
                9.30. Os itens deverão ser entregues diretamente nas escolas, conformes os pedidos;
                <br><br>
                9.31. As entregas correrão de forma semanal;
                <br><br>
                9.32. As entregas de carnes e frios deverão ser feitas em veículos refrigerados
                objetivando a entrega adequada dos itens, os demais itens deverão ser transportados em
                caminhão tipo baú específico para esse fim, devendo ser previamente higienizados e não
                conter qualquer substância que possa acarretar lesão física, química ou biológica aos
                alimentos.
                <br><br>
                9.33. O recebimento do objeto não exclui a responsabilidade da contratada pelos
                prejuízos resultantes da incorreta execução do contrato.
                <br><br>
                9.34. No caso de produtos perecíveis, o prazo de validade na data da entrega não poderá
                ser inferior a 30 (trinta) dias do prazo total recomendado pelo fabricante.
                <br><br>
                9.35. Os gêneros deverão estar sobrepostos em pallets e/ou em caixa de polietileno
                higienizadas quando necessário, não sendo permitido o transporte de hortifrútis em caixas
                de madeira ou papelão, com exceção dos ovos que poderão ser acondicionados em
                embalagem de papelão e/ou isopor, e/ou polietileno atóxico.
                <br><br>
                9.36. Os entregadores deverão estar devidamente identificados com o nome da
                empresa, uniformizados (camisa, sapato, calça, crachá, boné) com hábitos de higiene
                satisfatórios (uniforme limpos, higiene pessoal adequada) conforme boas práticas de
                fabricação/produção de alimentos possuindo boa conduta e relacionamento no local de
                entrega.
                <br><br>
            @endif
            @if ($processo->finalizacao->veiculos === "Sim")
                @if ($processo->tipo_procedimento === 'SERVIÇOS')

                    @if ($processo->finalizacao->locacao_veiculo === "Sim")
                        9.28. Atender à legislação vigente da ANTT, DENATRAN, DETRAN/PI e demais
                        órgãos que regulam e fiscalizam o trânsito e o fretamento de veículos;<br><br>
                        9.29. Dispor de seguro veicular regido pela legislação vigente no Brasil;<br><br>
                        9.30. Arcar com toda e qualquer multa sobre descumprimento de legislação em
                        vigor;<br><br>
                        9.31. Disponibilizar os veículos dentro das especificações contidas neste Termo
                        de Referência e conforme as especificações discriminadas em sua proposta,
                        segurados, licenciados, sem pendências tributárias, em perfeitas condições de
                        utilização, conservação, trafegabilidade, funcionamento e segurança,
                        obedecendo a todas as exigências estabelecidas pelas legislações de trânsito e
                        ambiental.<br><br>
                        9.32. Responsabilizar-se por todas as despesas dos veículos utilizados na
                        execução dos serviços, tais como licenciamento, seguro total, manutenção e
                        outras que incidam direta ou indiretamente sobre os serviços ora contratados,
                        inclusive acidente, para o que os veículos deverão estar segurados.<br><br>
                        9.33. Efetuar a entrega do veículo com peças somente novas e originais ou de
                        desempenho iguais ou superiores as utilizadas na fabricação do veículo;<br><br>
                        9.34. A contratada obriga-se a substituir os veículos quebrados ou defeituosos
                        no prazo de até 24 (vinte e quatro) horas após a constatação do fato, a contar da
                        comunicação efetuada pela contratante;<br><br>
                        9.35. O seguro deverá possuir, no mínimo, proteção total com franquia de acordo
                        com valores praticado pelas seguradoras, nos casos de colisão furto ou incêndio
                        ou perda total;<br><br>
                        9.36. Permitir que a Prefeitura Municipal de {{ $processo->prefeitura->cidade }} inspecione o(s)
                        veículo(s) objeto desta licitação, no ato da entrega, ficando assegurado à
                        Prefeitura Municipal o direito de aceitá-los ou não;
                    @else
                        9.28. Para a Prestação dos serviços, os veículos deverão, obrigatoriamente,
                        seguir as devidas exigências: <br>

                        • cinto de segurança, conforme regulamentação específica do CONTRAN, <br>
                        • para os veículos de transporte e de condução escolar, os de transporte de
                        passageiros com mais de dez lugares e os de carga com peso bruto total
                        superior a quatro mil, quinhentos e trinta e seis quilogramas, equipamento
                        registrador instantâneo inalterável de velocidade e tempo;<br>
                        • encosto de cabeça, para todos os tipos de veículos automotores, segundo
                        normas estabelecidas pelo CONTRAN;<br>
                        • dispositivo destinado ao controle de emissão de gases poluentes e de ruído,
                        segundo normas estabelecidas pelo CONTRAN.<br>
                        • equipamento suplementar de retenção - air bag frontal para o condutor e o
                        passageiro do banco dianteiro.<br>
                        • luzes de rodagem diurna.  Deverá estar com licenciamento em dias,
                        conforme as normas do executivo do Estado do Piauí ou onde estiver
                        registrado o veículo que executará a condução;<br>
                        • Demais requisitos de autorização para condução:<br>
                        • Estar registrado como veículo de passageiros;<br>
                        • Deverão estar em dia com a inspeção semestral para verificação dos
                        equipamentos obrigatórios e de segurança;<br>
                        • Deverá possuir pintura de faixa horizontal na cor amarela, com quarenta
                        centímetros de largura, à meia altura, em toda a extensão das partes laterais
                        e traseira da carroçaria, com o dístico ESCOLAR, em preto, sendo que, em caso
                        de veículo de carroçaria pintada na cor amarela, as cores aqui indicadas
                        devem ser invertidas;<br>
                        • Possuir equipamento registrador instantâneo inalterável de velocidade e
                        tempo;<br>
                        • Possuir lanternas de luz branca, fosca ou amarela dispostas nas
                        extremidades da parte superior dianteira e lanternas de luz vermelha
                        dispostas na extremidade superior da parte traseira;<br>
                        • Possuir cintos de segurança em número igual à lotação;<br>
                        • Possuir quaisquer outros requisitos e equipamentos obrigatórios
                        estabelecidos pelo CONTRAN.<br>
                        • As exigências acima, assim como, as especificações da lotação deverão estar
                        visíveis, sendo vedado a condução em número superior de alunos ao
                        especificado.<br>
                        • Das exigências em relação ao condutor:<br>
                        • Ter idade superior a vinte e um anos;<br>
                        • Ser habilitado na categoria D;<br>
                        • Não ter cometido mais de uma infração gravíssima nos 12 (doze) últimos
                        meses;<br>
                        • Ser aprovado em curso especializado, nos termos da regulamentação do
                        CONTRAN.<br>
                        • Os condutores dos veículos, com base nas exigências anteriores, para
                        exercerem suas atividades, deverão apresentar, previamente, certidão
                        negativa do registro de distribuição criminal relativamente aos crimes de
                        homicídio, roubo, estupro e corrupção de menores, renovável a cada cinco
                        anos, junto ao órgão responsável pela respectiva concessão ou autorização.<br>
                        • Conforme a recomendação do FNDE, o veículo a ser utilizado nesta rota
                        não poderá ter vida útil superior a 10 (dez) anos, conforme Resolução nº
                        1, de 20 de abril de 2021
                    @endif
                @else
                    9.28. O objeto deve ser entregue na sede da Prefeitura Municipal de {{ $processo->prefeitura->cidade }},
                    localizada na {{ $processo->prefeitura->endereco }}, no horário de 08h às 13h, ou em
                    local indicado pela administração, o objeto deve ser entregue conforme Ordem de
                    Fornecimento.
                    <br><br>
                    9.29. Para efeito de esclarecimento, a entrega do objeto deve ser feita utilizando
                    caminhão plataforma do tipo reboque, guincho ou cegonha, devendo o Fiscal de Contrato
                    proceder com a conferência do objeto no ato da entrega.
                    <br><br>
                    9.30. Antes da entrega do objeto, este não poderá sofrer deslocamento próprio (o veículo
                    somente pode ser transportado em caminhão plataforma do tipo reboque), devendo ser
                    entregue com quilometragem “zero” ou com a quilometragem registrada que seja
                    decorrente de atividades de transporte ou de deslocamento no pátio da fábrica ou da
                    própria empresa fornecedora.
                    <br><br>
                    9.31. Entregar, juntamente com os veículos, o manual, certificados de garantia do
                    fabricante, notas fiscais e a relação da rede autorizada pelo fabricante;
                    <br><br>
                    9.32. Providenciar, independentemente de ser fabricante ou não fabricante, a correção
                    ou substituição do todo ou em parte do material, peça, componente ou acessório, que
                    apresente defeitos de fabricação ou divergência com as especificações estabelecidas no
                    edital, sem ônus para administração, observando o contrato e a legislação vigente.
                @endif
            @endif
        </p>

        <div style="margin-bottom: 20px;">
            <img src="{{ public_path('icons/descricao-necessidade.png') }}" width="30px"
            alt="DESCRIÇÃO DA NECESSIDADE">
            <h4 style="display: inline-block; margin: 0 0 0 10px; vertical-align: middle;">
                CLÁUSULA DÉCIMA– GARANTIA DE EXECUÇÃO</h4>
        </div>

        <p style="text-align: justify;">
            10.1. Não haverá exigência de garantia contratual da execução
        </p>

        <div style="margin-bottom: 20px;">
            <img src="{{ public_path('icons/descricao-necessidade.png') }}" width="30px"
            alt="DESCRIÇÃO DA NECESSIDADE">
            <h4 style="display: inline-block; margin: 0 0 0 10px; vertical-align: middle;">
                CLÁUSULA DÉCIMA PRIMEIRA – INFRAÇÕES E SANÇÕES ADMINISTRATIVAS</h4>
        </div>
        <div style="text-align: justify;">
            <p>11.1. Comete infração administrativa, nos termos da Lei nº 14.133/2021, o contratado que:</p>
            <div style="margin-left: 30px;">
                a) der causa à inexecução parcial do contrato;
                b) der causa à inexecução parcial do contrato que cause grave dano à Administração ou ao funcionamento dos serviços públicos ou ao interesse coletivo;
                c) der causa à inexecução total do contrato;
                d) ensejar o retardamento da execução ou da entrega do objeto da contratação sem motivo justificado;
                e) apresentar documentação falsa ou prestar declaração falsa durante a execução do contrato;
                f) praticar ato fraudulento na execução do contrato;
                g) comportar-se de modo inidôneo ou cometer fraude de qualquer natureza;
                h) praticar ato lesivo previsto no art. 5º da Lei nº 12.846/2013.
            </div>

            <p>Serão aplicadas ao contratado que incorrer nas infrações acima descritas as seguintes sanções:</p>
            <div style="margin-left: 30px;">
                i. <span style="font-weight: bold">Advertência</span>, quando o contratado der causa à inexecução parcial do contrato,
                sempre que não se justificar a imposição de penalidade mais grave (art. 156, §2º,
                da Lei nº 14.133, de 2021);
                <br><br>
                ii.<span style="font-weight: bold">Impedimento de licitar e contratar</span>, quando praticadas as condutas descritas
                nas alíneas “b”, “c” e “d” do subitem acima deste Contrato, sempre que não se
                justificar a imposição de penalidade mais grave (art. 156, § 4º, da Lei nº 14.133, de
                2021);
                <br><br>
                iii.<span style="font-weight: bold">Declaração de inidoneidade para licitar e contratar</span>, quando praticadas as
                condutas descritas nas alíneas “e”, “f”, “g” e “h” do subitem acima deste
                Contrato, bem como nas alíneas “b”, “c” e “d”, que justifiquem a imposição de
                penalidade mais grave (art. 156, §5º, da Lei nº 14.133, de 2021).
                <br><br>
                iv.<span style="font-weight: bold">Multa:</span>
                <br><br>
                <div style="margin-left: 30px;">
                    1. moratória de 0,5 % (zero virgula cinco por cento) por dia de atraso
                    injustificado sobre o valor da parcela inadimplida, até o limite de 60
                    (sessenta) dias;
                    <br><br>
                    2. moratória de 0,5 % (zero virgula cinco por cento) por dia de atraso
                    injustificado sobre o valor total do contrato, até o máximo de 30% (trinta por
                    cento), pela inobservância do prazo fixado para apresentação,
                    suplementação ou reposição da garantia.
                    <br><br>
                    i.O atraso superior a 30 (trinta) dias autoriza a Administração a promover a
                    extinção do contrato por descumprimento ou cumprimento irregular de suas
                    cláusulas, conforme dispõe o inciso I do art. 137 da Lei n. 14.133, de 2021.
                </div>
            </div>

            <p>
                11.3. A aplicação das sanções previstas neste Contrato não exclui, em hipótese alguma,
                a obrigação de reparação integral do dano causado ao Contratante (art. 156, §9º, da Lei nº
                14.133, de 2021) <br><br>
                11.4. Todas as sanções previstas neste Contrato poderão ser aplicadas
                cumulativamente com a multa (art. 156, §7º, da Lei nº 14.133, de 2021).<br><br>
                11.4.1. Antes da aplicação da multa será facultada a defesa do interessado no prazo de 15
                (quinze) dias úteis, contado da data de sua intimação (art. 157, da Lei nº 14.133, de 2021)<br><br>
                11.4.2. Se a multa aplicada e as indenizações cabíveis forem superiores ao valor do
                pagamento eventualmente devido pelo Contratante ao Contratado, além da perda desse
                valor, a diferença será descontada da garantia prestada ou será cobrada judicialmente (art.
                156, §8º, da Lei nº 14.133, de 2021).<br><br>
                11.4.3. Previamente ao encaminhamento à cobrança judicial, a multa poderá ser recolhida
                administrativamente no prazo máximo de 30 (trinta) dias, a contar da data do recebimento
                da comunicação enviada pela autoridade competente.
            </p>

            <p>
                11.5. A aplicação das sanções realizar-se-á em processo administrativo que assegure o
                contraditório e a ampla defesa ao Contratado, observando-se o procedimento previsto no
                caput e parágrafos do art. 158 da Lei nº 14.133, de 2021, para as penalidades de
                impedimento de licitar e contratar e de declaração de inidoneidade para licitar ou
                contratar.
            </p>

            <p>
                11.6. Na aplicação das sanções serão considerados (art. 156, §1º, da Lei nº 14.133, de 2021):
            </p>
            <div style="margin-left: 30px;">
                a) a natureza e a gravidade da infração cometida;<br>
                b) as peculiaridades do caso concreto;<br>
                c) as circunstâncias agravantes ou atenuantes;<br>
                d) os danos que dela provierem para o Contratante;<br>
                e) a implantação ou o aperfeiçoamento de programa de integridade, conforme
                normas e orientações dos órgãos de controle.
            </div>

            <p>
                11.7. Os atos previstos como infrações administrativas na Lei nº 14.133, de 2021, ou em
                outras leis de licitações e contratos da Administração Pública que também sejam
                tipificados como atos lesivos na Lei nº 12.846, de 2013, serão apurados e julgados
                conjuntamente, nos mesmos autos, observados o rito procedimental e autoridade
                competente definidos na referida Lei (art. 159).
            </p>

            <p>
                11.8. A personalidade jurídica do Contratado poderá ser desconsiderada sempre que
                utilizada com abuso do direito para facilitar, encobrir ou dissimular a prática dos atos
                ilícitos previstos neste Contrato ou para provocar confusão patrimonial, e, nesse caso,
                todos os efeitos das sanções aplicadas à pessoa jurídica serão estendidos aos seus
                administradores e sócios com poderes de administração, à pessoa jurídica sucessora ou
                à empresa do mesmo ramo com relação de coligação ou controle, de fato ou de direito,
                com o Contratado, observados, em todos os casos, o contraditório, a ampla defesa e a
                obrigatoriedade de análise jurídica prévia (art. 160, da Lei nº 14.133, de 2021).
            </p>

            <p>
                11.9. O Contratante deverá, no prazo máximo 15 (quinze) dias úteis, contado da data de
                aplicação da sanção, informar e manter atualizados os dados relativos às sanções por ela
                aplicadas, para fins de publicidade no Cadastro Nacional de Empresas Inidôneas e
                Suspensas (Ceis) e no Cadastro Nacional de Empresas Punidas (Cnep.
            </p>

            <p>
                11.10. As sanções de impedimento de licitar e contratar e declaração de inidoneidade
                para licitar ou contratar são passíveis de reabilitação na forma do art. 163 da Lei nº
                14.133/21.
            </p>

            <p>
                11.11. Os débitos do contratado para com a Administração contratante, resultantes de
                multa administrativa e/ou indenizações, não inscritos em dívida ativa, poderão ser
                compensados, total ou parcialmente, com os créditos devidos pelo referido órgão
                decorrentes deste mesmo contrato ou de outros contratos administrativos que o
                contratado possua com o mesmo órgão ora contratante.
            </p>

        </div>

        <div style="margin-bottom: 20px;">
            <img src="{{ public_path('icons/descricao-necessidade.png') }}" width="30px"
            alt="DESCRIÇÃO DA NECESSIDADE">
            <h4 style="display: inline-block; margin: 0 0 0 10px; vertical-align: middle;">
                CLÁUSULA DÉCIMA SEGUNDA– DA EXTINÇÃO CONTRATUAL</h4>
        </div>

        <p style="text-align: justify;">
            12.1. O contrato se extingue quando cumpridas as obrigações de ambas as partes, ainda
            que isso ocorra antes do prazo estipulado para tanto. <br><br>
            12.2. Se as obrigações não forem cumpridas no prazo estipulado, a vigência ficará
            prorrogada até a conclusão do objeto, caso em que deverá a Administração providenciar a
            readequação do cronograma fixado para o contrato.<br><br>
            12.2.1. Quando a não conclusão do contrato referida no item anterior decorrer de culpa do
            contratado:<br><br>
            <div style="margin-left: 30px;">
                a) ficará ele constituído em mora, sendo-lhe aplicáveis as respectivas sanções
                administrativas; <br><br>
                b) poderá a Administração optar pela extinção do contrato e, nesse caso, adotará as
                medidas admitidas em lei para a continuidade da execução contratual.<br><br>
            </div>
            12.3. O contrato pode ser extinto antes de cumpridas as obrigações nele estipuladas, ou
            antes do prazo nele fixado, por algum dos motivos previstos no artigo 137 da Lei nº
            14.133/21, bem como amigavelmente, assegurados o contraditório e a ampla defesa. <br><br>
            12.3.1. Nesta hipótese, aplicam-se também os artigos 138 e 139 da mesma Lei.<br><br>
            12.3.2. A alteração social ou a modificação da finalidade ou da estrutura da empresa não
            ensejará a rescisão se não restringir sua capacidade de concluir o contrato.<br><br>
            12.3.2.1. Se a operação implicar mudança da pessoa jurídica contratada, deverá ser
            formalizado termo aditivo para alteração subjetiva.<br><br>
            12.4. O termo de rescisão, sempre que possível, será precedido:<br><br>
            12.4.1. Balanço dos eventos contratuais já cumpridos ou parcialmente cumpridos;<br><br>
            12.4.2. Relação dos pagamentos já efetuados e ainda devidos;<br><br>
            12.4.3. Indenizações e multas.<br><br>
            12.5. A extinção do contrato não configura óbice para o reconhecimento do desequilíbrio
            econômico-financeiro, hipótese em que será concedida indenização por meio de termo
            indenizatório (art. 131, caput, da Lei n.º 14.133, de 2021).
        </p>

        <div style="margin-bottom: 20px;">
            <img src="{{ public_path('icons/descricao-necessidade.png') }}" width="30px"
            alt="DESCRIÇÃO DA NECESSIDADE">
            <h4 style="display: inline-block; margin: 0 0 0 10px; vertical-align: middle;">
                CLÁUSULA DÉCIMA TERCEIRA – DOTAÇÃO ORÇAMENTÁRIA</h4>
        </div>

        <p style="text-align: justify;">
            13.1. As despesas decorrentes da presente contratação correrão à conta de recursos
            específicos consignados no Orçamento Geral deste exercício, na dotação abaixo
            discriminada:
        </p>
        <table style="border-collapse: collapse; width: 100%; border: 1px solid black;">
            <tr>
                <!-- Coluna da esquerda -->
                <td style="vertical-align: top; padding: 10px;">
                    {!! str_replace('<p>', '<p style="text-indent:30px; text-align: justify;">', $processo->contrato->fonte_recurso) !!}
                </td>
            </tr>
        </table>
        <p style="text-align: justify;">
            13.2. A dotação relativa aos exercícios financeiros subsequentes será indicada após
            aprovação da Lei Orçamentária respectiva e liberação dos créditos correspondentes,
            mediante apostilamento.
        </p>

        <div style="margin-bottom: 20px;">
            <img src="{{ public_path('icons/descricao-necessidade.png') }}" width="30px"
            alt="DESCRIÇÃO DA NECESSIDADE">
            <h4 style="display: inline-block; margin: 0 0 0 10px; vertical-align: middle;">
                CLÁUSULA DÉCIMA QUARTA – DOS CASOS OMISSOS</h4>
        </div>

        <p style="text-align: justify;">
            14.1. Os casos omissos serão decididos pelo contratante, segundo as disposições
            contidas na Lei nº 14.133, de 2021, e demais normas federais aplicáveis e,
            subsidiariamente, segundo as disposições contidas na Lei nº 8.078, de 1990 – Código de
            Defesa do Consumidor – e normas e princípios gerais dos contratos.
        </p>
        <div style="margin-bottom: 20px;">
            <img src="{{ public_path('icons/descricao-necessidade.png') }}" width="30px"
            alt="DESCRIÇÃO DA NECESSIDADE">
            <h4 style="display: inline-block; margin: 0 0 0 10px; vertical-align: middle;">
                CLÁUSULA DÉCIMA QUINTA – DA SUBCONTRATAÇÃO:</h4>
        </div>

        <p style="text-align: justify;">
            15.1 É expressamente vedado à CONTRATADA transferir a terceiros as obrigações
            assumidas neste contrato, sem expressa anuência do Município de {{ $processo->prefeitura->cidade }}.
        </p>
        <div style="margin-bottom: 20px;">
            <img src="{{ public_path('icons/descricao-necessidade.png') }}" width="30px"
            alt="DESCRIÇÃO DA NECESSIDADE">
            <h4 style="display: inline-block; margin: 0 0 0 10px; vertical-align: middle;">
                CLÁUSULA DÉCIMA QUINTA – ALTERAÇÕES</h4>
        </div>

        <p style="text-align: justify;">
            15.1. Eventuais alterações contratuais reger-se-ão pela disciplina dos arts. 124 e
            seguintes da Lei nº 14.133, de 2021. <br><br>
            15.2. O contratado é obrigado a aceitar, nas mesmas condições contratuais, os
            acréscimos ou supressões que se fizerem necessários, até o limite de 25% (vinte e cinco
            por cento) do valor inicial atualizado do contrato. <br><br>
            15.3. Registros que não caracterizam alteração do contrato podem ser realizados por
            simples apostila, dispensada a celebração de termo aditivo, na forma do art. 136 da Lei nº
            14.133, de 2021.
        </p>

        <div style="margin-bottom: 20px;">
            <img src="{{ public_path('icons/descricao-necessidade.png') }}" width="30px"
            alt="DESCRIÇÃO DA NECESSIDADE">
            <h4 style="display: inline-block; margin: 0 0 0 10px; vertical-align: middle;">
                CLÁUSULA DÉCIMA SEXTA – PUBLICAÇÃO</h4>
        </div>

        <p style="text-align: justify;">
            16.1. Incumbirá ao contratante divulgar o presente instrumento no Portal Nacional de
            Contratações Públicas (PNCP), na forma prevista no art. 94 da Lei 14.133, de 2021, bem
            como no respectivo sítio oficial na Internet.
        </p>

        <div style="margin-bottom: 20px;">
            <img src="{{ public_path('icons/descricao-necessidade.png') }}" width="30px"
            alt="DESCRIÇÃO DA NECESSIDADE">
            <h4 style="display: inline-block; margin: 0 0 0 10px; vertical-align: middle;">
                17. CLÁUSULA DÉCIMA SÉTIMA– DA ALOCAÇÃO DE RISCOS</h4>
        </div>

        <p style="text-align: justify;">
            17.1. São de responsabilidade das partes, sem prejuízo das demais obrigações
            constantes neste Contrato e no Termo de Referência, os riscos relacionados oriundos
            deste contrato, conforme tenha sido prevista matriz de riscos para a sua execução.
            <br><br>
            17.2. Caso as situações descritas na matriz de riscos venham a ocorrer, poderão ser
            adotadas as providências a seguir:
            <br><br>
            17.2.1. Utilização de seguros obrigatórios previamente definidos no contrato;
            <br>
            17.2.2. Restabelecimento da equação econômico-financeira do contrato nos
            casos em que o sinistro seja considerado na matriz de riscos como causa de
            desequilíbrio não suportada pela parte que pretenda o restabelecimento;
            <br>
            17.2.3. Resolução do contrato quando o sinistro majorar excessivamente ou
            impedir a continuidade da execução contratual.
            <br><br>
            17.3. As providências elencadas no item 17.2 somam-se àquelas decorrentes das
            peculiaridades da contratação.
        </p>
        <div style="margin-bottom: 20px;">
            <img src="{{ public_path('icons/descricao-necessidade.png') }}" width="30px"
            alt="DESCRIÇÃO DA NECESSIDADE">
            <h4 style="display: inline-block; margin: 0 0 0 10px; vertical-align: middle;">
                CLÁUSULA DÉCIMA OITAVA– FORO</h4>
        </div>

        <p style="text-align: justify;">
            18.1. Fica eleito o Foro da Justiça de {{ $processo->contrato->comarca }}, para dirimir os litígios que decorrerem da
            execução deste Termo de Contrato que não puderem ser compostos pela conciliação,
            conforme art. 92, §1º, da Lei nº 14.133/21.
        </p>

    </div>
    {{-- Bloco de data e assinatura --}}
    <div class="footer-signature">
        {{ $processo->prefeitura->cidade }},
        {{ \Carbon\Carbon::parse($dataSelecionada)->translatedFormat('d \d\e F \d\e Y') }}
    </div>

    @if ($hasSelectedAssinantes)
        {{-- Renderiza APENAS O PRIMEIRO assinante da lista --}}
        @php
            $primeiroAssinante = $assinantes[0]; // Pega o segundo item
        @endphp

        <div style="margin-top: 40px; text-align: center;">
            <div class="signature-block" style="display: inline-block; margin: 0 40px;">
                ___________________________________<br>
                <p style="line-height: 1.2;">
                    {{ $primeiroAssinante['responsavel'] }} <br>
                    <span>{{ $primeiroAssinante['unidade_nome'] }}</span>
                </p>
            </div>
        </div>
        <div style="margin-top: 40px; text-align: center;">
            <div class="signature-block" style="display: inline-block; margin: 0 40px;">
                ___________________________________<br>
                <p style="line-height: 1.2;">
                    {{ $dadosContratado['razao_social'] }} <br>
                    {{ $dadosContratado['representante'] }} <br>
                    {{ $dadosContratado['cpf_representante_formatado'] }} <br>
                </p>
            </div>
        </div>
    @else
        {{-- Bloco Padrão (Fallback) --}}
        <div class="signature-block" style="margin-top: 40px; text-align: center;">
            ___________________________________<br>
            <p style="line-height: 1.2;">
                <span style="color: red;">[Cargo/Título Padrão - A ser ajustado]</span>
            </p>
        </div>
    @endif

    TESTEMUNHAS<br><br>

    ___________________________________<br><br><br>
    ___________________________________

    {{-- QUEBRA DE PÁGINA --}}
    <div class="page-break"></div>

    <div>
        <table style="width:100%; border-collapse:collapse; font-size:10px; margin-bottom:20px; " border="1">

            <!-- Cabeçalho -->
            <tr>
                <td colspan="2" style="padding:8px; text-align:center; font-weight:bold;">
                    EXTRATO DO CONTRATO Nº {{ $campos['numero_extrato'] }}<br>
                    PROCESSO ADMINISTRATIVO Nº {{ $processo->numero_processo }}<br>
                    MODALIDADE: PREGÃO ELETRÔNICO Nº {{ $processo->numero_procedimento }}
                </td>
            </tr>

            <!-- OBJETO -->
            <tr>
                <td style="padding:6px; width:30%; font-weight:bold;">
                    OBJETO:
                </td>
                <td style="padding:6px;">
                    {!! strip_tags($processo->objeto) !!}
                </td>
            </tr>

            <!-- CONTRATANTE -->
            <tr>
                <td style="padding:6px; font-weight:bold;">
                    CONTRATANTE:
                </td>
                <td style="padding:6px;">
                   {{ $processo->finalizacao->orgao_responsavel }}
                </td>
            </tr>

            <!-- CONTRATADO -->
            <tr>
                <td style="padding:6px; font-weight:bold;">
                    CONTRATADO:
                </td>
                <td style="padding:6px;">
                     {{ $dadosContratado['razao_social'] }}
                </td>
            </tr>

            <!-- CNPJ -->
            <tr>
                <td style="padding:6px; font-weight:bold;">
                    CNPJ (CONTRATADO):
                </td>
                <td style="padding:6px;">
                     {{ $dadosContratado['cnpj_formatado'] }}
                </td>
            </tr>

            <!-- VALOR -->
            <tr>
                <td style="padding:6px; font-weight:bold;">
                    VALOR:
                </td>
                <td style="padding:6px;">
                    R$ {{ number_format($valorTotalContrato, 2, ',', '.') }}
                </td>
            </tr>

            <!-- VIGÊNCIA -->
            @php
                $vigencia = is_array($processo->detalhe->prazo_vigencia ?? null)
                    ? $processo->detalhe->prazo_vigencia
                    : ['12_meses'];

                $outro_vigencia = $processo->detalhe->prazo_vigencia_outro ?? '________________.';

                // Texto final da vigência
                if (in_array('exercicio_financeiro', $vigencia)) {
                    $textoVigenciaTabela = "Até 31/12 do exercício financeiro da contratação";
                } elseif (in_array('12_meses', $vigencia)) {
                    $textoVigenciaTabela = "12 meses";
                } elseif (in_array('outro', $vigencia)) {
                    $textoVigenciaTabela = $outro_vigencia;
                } else {
                    $textoVigenciaTabela = "________________";
                }
            @endphp
            <tr>
                <td style="padding:6px; font-weight:bold;">
                    VIGÊNCIA:
                </td>
                <td style="padding:6px;">
                    {{ $textoVigenciaTabela }}
                </td>
            </tr>


            <!-- FONTE DOS RECURSOS -->
            <tr>
                <td style="padding:6px; font-weight:bold;">
                    FONTE DOS RECURSOS:
                </td>
                <td style="padding:6px;">
                    {!! strip_tags($processo->contrato->fonte_recurso) !!}
                </td>
            </tr>

            <!-- FUNDAMENTAÇÃO LEGAL -->
            <tr>
                <td style="padding:6px; font-weight:bold;">
                    FUNDAMENTAÇÃO LEGAL:
                </td>
                <td style="padding:6px; text-align:justify;">
                    Será regida pelas normas fixadas no PREGÃO ELETRÔNICO nº {{ $processo->numero_procedimento }},
                    e pela Lei 14.133/21, de 1 de abril de 2021, e legislação posterior,
                    que o suplementam no que for omisso.
                </td>
            </tr>

            <!-- ASSINATURA (CONTRATANTE) -->
            <tr>
                <td style="padding:6px; font-weight:bold;">
                    ASSINATURA (CONTRATANTE):
                </td>
                <td style="padding:6px;">
                    {{ $primeiroAssinante['responsavel'] }}
                </td>
            </tr>

            <!-- ASSINATURA (CONTRATADO) -->
            <tr>
                <td style="padding:6px; font-weight:bold;">
                    ASSINATURA (CONTRATADO):
                </td>
                <td style="padding:6px;">
                    {{ $dadosContratado['representante'] }}
                </td>
            </tr>

            <!-- DATA DA ASSINATURA -->
            <tr>
                <td style="padding:6px; font-weight:bold;">
                    DATA DA ASSINATURA:
                </td>
                <td style="padding:6px;">
                    {{ $dataAssinaturaFormatada }}
                </td>
            </tr>

        </table>
    </div>

</body>

</html>
