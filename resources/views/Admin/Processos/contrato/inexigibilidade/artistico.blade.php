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

    @php
        // Definido aqui (e não dentro do @if de assinaturas) porque o EXTRATO,
        // no fim do documento, também referencia o assinante.
        $hasSelectedAssinantes = isset($assinantes) && count($assinantes) > 0;
        $primeiroAssinante = $hasSelectedAssinantes ? $assinantes[0] : null;
    @endphp

    <div>
        <h4 style="text-align: center;">
            CONTRATO Nº {{ $processo->contrato->numero_contrato }}
        </h4>

        <table style="width:100%; table-layout:fixed; border-collapse:collapse;">
            <tr>
                <td style="width:40%; padding:8px; vertical-align:top; word-wrap:break-word; white-space:normal;">
                <!-- Conteúdo da primeira célula -->
                </td>
                <td style="width:60%; padding:8px; vertical-align:top; word-wrap:break-word; white-space:normal;">
                    Termo de Contrato de prestação de serviços que entre si fazem o
                    {{ mb_strtoupper($processo->prefeitura->nome) }} e
                    {{ mb_strtoupper($processo->detalhe->razao_social ?? '') }}.
                </td>
            </tr>
        </table>

        <!-- Contratante -->
        <div class="section">
            <table>
                <tr>
                    <td class="icon">
                        <img src="{{ public_path('icons/Imagem1.png') }}" width="40">
                    </td>
                    <td class="content">
                        <div style=" font-weight: bold; margin-bottom: 3px;">Contratante</div>
                        <div style="">
                            {{ $processo->prefeitura->nome }}, com sede no(a)
                            {{ $processo->prefeitura->endereco }}, na cidade de {{ $processo->prefeitura->cidade }}
                            inscrito(a) no CNPJ
                            sob o nº {{ $processo->prefeitura->cnpj }}, neste ato representado(a) pelo(a)
                            {{ $processo->detalhe->responsavel }} inscrito no CPF sob n°
                            {{ $processo->detalhe->cpf_responsavel }}.
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Contratado -->
        <div class="section">
            <table>
                <tr>
                    <td class="icon">
                        <img src="{{ public_path('icons/Imagem1.png') }}" width="40">
                    </td>
                    <td class="content">
                        <div style=" font-weight: bold; margin-bottom: 3px;">Contratado</div>
                        <div style="">
                            {{ $processo->detalhe->razao_social }}, inscrito(a) no CNPJ sob o nº
                            {{ $processo->detalhe->cnpj_empresa_vencedora }}, sediado(a) na
                            {{ $processo->detalhe->endereco_empresa_vencedora }}, neste
                            ato representado(a) por {{ $processo->detalhe->representante_legal_empresa }}, inscrito
                            no CPF sob n° {{ $processo->detalhe->cpf_representante }}.
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <p style="text-align: justify;">
            Tendo em vista o que consta no Processo administrativo n° {{ $processo->numero_processo }} e em observância às disposições da Lei
            n° 14.133, de 2021 e na Lei n° 8.078, de 1990 - Código de Defesa do Consumidor, resolvem celebrar o
            presente Termo de Contrato, decorrente da Inexigibilidade de licitação n° {{ $processo->numero_procedimento }}, mediante as cláusulas e
            condições a seguir enunciadas.
        </p>

        {{-- ══════════════ CLÁUSULA PRIMEIRA ══════════════ --}}
        <div style="margin-bottom: 20px;">
            <img src="{{ public_path('icons/descricao-necessidade.png') }}" width="30px" alt="">
            <h4 style="display: inline-block; margin: 0 0 0 10px; vertical-align: middle;">CLÁUSULA PRIMEIRA – DO OBJETO E REGIME DE EXECUÇÃO</h4>
        </div>
        <p style="text-align: justify;">
            1.1. O objeto do presente Termo de contrato é a {!! strip_tags($processo->objeto) !!}.
            <br>1.2. Todos os termos do Termo de Referência e da proposta da contratada integram o presente contrato em
            todas as suas condições.
            <br>1.3. Em caso de divergência entre o disposto no Termo de Referência e o presente instrumento contratual,
            prevalecerá o acordado neste contrato, por tratar-se de documento posterior, mais específico e devidamente
            assinado por ambas as partes, de modo que não serão consideradas válidas, exigíveis ou oponíveis quaisquer
            obrigações, exigências, condições ou responsabilidades constantes exclusivamente no Termo de Referência que
            não estejam expressamente incorporadas a este contrato.
        </p>

        {{-- ══════════════ CLÁUSULA SEGUNDA ══════════════ --}}
        <div style="margin-bottom: 20px;">
            <img src="{{ public_path('icons/descricao-necessidade.png') }}" width="30px" alt="">
            <h4 style="display: inline-block; margin: 0 0 0 10px; vertical-align: middle;">CLÁUSULA SEGUNDA - DA EXECUÇÃO DO CONTRATO</h4>
        </div>
        <p style="text-align: justify;">
            2.1. Os serviços serão executados em conformidade com a proposta apresentada pela CONTRATADA, vez
            compõe, em todos os seus termos, o processo administrativo n° {{ $processo->numero_processo }} e inexigibilidade de licitação
            {{ $processo->numero_procedimento }}.
        </p>

        {{-- ══════════════ CLÁUSULA TERCEIRA ══════════════ --}}
        <div style="margin-bottom: 20px;">
            <img src="{{ public_path('icons/descricao-necessidade.png') }}" width="30px" alt="">
            <h4 style="display: inline-block; margin: 0 0 0 10px; vertical-align: middle;">CLÁUSULA TERCEIRA - DO PRAZO</h4>
        </div>
        <p style="text-align: justify;">
            3.1. O prazo de vigência deste Termo de Contrato tem início na data de {{ \Carbon\Carbon::parse($processo->detalhe->prazo_inicio_prestacao_servico)->format('d/m/Y') }} e encerramento em
            {{ \Carbon\Carbon::parse($processo->detalhe->prazo_final_prestacao_servico)->format('d/m/Y') }}, sendo que em caso de eventual necessidade de prorrogação, decorrente de acordo entre as
            partes, será formalizado o respectivo Aditivo contratual.
        </p>

        {{-- ══════════════ CLÁUSULA QUARTA ══════════════ --}}
        <div style="margin-bottom: 20px;">
            <img src="{{ public_path('icons/descricao-necessidade.png') }}" width="30px" alt="">
            <h4 style="display: inline-block; margin: 0 0 0 10px; vertical-align: middle;">CLÁUSULA QUARTA - DO PREÇO E FORMA DE PAGAMENTO</h4>
        </div>
        <p style="text-align: justify;">
            4.1. O valor do presente Termo de Contrato é de R$ {{ $detalhe->valor_estimado }};
            <br>4.2. No valor acima estão incluídas todas as despesas ordinárias diretas e indiretas decorrentes da execução
            contratual, inclusive tributos e/ou impostos, encargos sociais, trabalhistas, previdenciários, fiscais e comerciais
            incidentes, taxa de administração, frete, seguro e outros necessários ao cumprimento integral do objeto da
            contratação, ressalvado o que for de responsabilidade do Contratante conforme Cláusula 9ª;
            <br>4.3. Os preços são fixos e irreajustáveis;
            <br>4.4. O pagamento será realizado mediante a apresentação da Fatura ou Nota Fiscal em 02 (duas) vias. O
            documento deverá ser submetido à aprovação do titular da Secretaria de Finanças e conter, em local de fácil
            identificação, os números do Processo Administrativo e do respectivo Contrato.
        </p>

        {{-- ══════════════ CLÁUSULA QUINTA ══════════════ --}}
        <div style="margin-bottom: 20px;">
            <img src="{{ public_path('icons/descricao-necessidade.png') }}" width="30px" alt="">
            <h4 style="display: inline-block; margin: 0 0 0 10px; vertical-align: middle;">CLÁUSULA QUINTA - DA DOTAÇÃO ORÇAMENTÁRIA</h4>
        </div>
        <p style="text-align: justify;">
            5.1. A Dotação orçamentária que correrá tal despesa é: {!! strip_tags($processo->detalhe->dotacao_orcamentaria) !!}, conforme disposto na Lei de meios vigente.
            <br>5.2. Sem prejuízo do disposto acima, compromete-se o CONTRATANTE de enviar à CONTRATADA, após a
            assinatura desta avença, a cópia da Nota de Empenho vinculada ao Serviço definido no objeto deste contrato,
            atestada e expedida pelo ordenador de despesas competentes do CONTRATANTE, para fins de conferência da
            CONTRATADA.
        </p>

        {{-- ══════════════ CLÁUSULA SEXTA ══════════════ --}}
        <div style="margin-bottom: 20px;">
            <img src="{{ public_path('icons/descricao-necessidade.png') }}" width="30px" alt="">
            <h4 style="display: inline-block; margin: 0 0 0 10px; vertical-align: middle;">CLÁUSULA SEXTA - DAS ALTERAÇÕES</h4>
        </div>
        <p style="text-align: justify;">
            6.1. Eventuais alterações contratuais reger-se-ão pela disciplina do art. 124 da Lei n° 14.133 de 2021;
            <br>6.2. As alterações contratuais deverão ser promovidas mediante celebração de termo aditivo, submetido à
            prévia aprovação da consultoria jurídica da CONTRATANTE, salvo nos casos de justificada necessidade de
            antecipação de seus efeitos, hipótese em que a formalização do aditivo deverá ocorrer no prazo máximo de
            1 (um) mês (art. 132 da Lei nº 14.133, de 2021);
            <br>6.3. Registros que não caracterizam alteração do contrato podem ser realizados por simples apostila,
            dispensada a celebração de termo aditivo, na forma do art. 136 da Lei nº 14.133, de 2021.
        </p>

        {{-- ══════════════ CLÁUSULA SÉTIMA ══════════════ --}}
        <div style="margin-bottom: 20px;">
            <img src="{{ public_path('icons/descricao-necessidade.png') }}" width="30px" alt="">
            <h4 style="display: inline-block; margin: 0 0 0 10px; vertical-align: middle;">CLÁUSULA SÉTIMA - FISCALIZAÇÃO</h4>
        </div>
        <p style="text-align: justify;">
            7.1. A fiscalização da execução do objeto será efetuada por Representante designado pela Secretaria demandante.
        </p>

        {{-- ══════════════ CLÁUSULA OITAVA ══════════════ --}}
        <div style="margin-bottom: 20px;">
            <img src="{{ public_path('icons/descricao-necessidade.png') }}" width="30px" alt="">
            <h4 style="display: inline-block; margin: 0 0 0 10px; vertical-align: middle;">CLÁUSULA OITAVA - OBRIGAÇÕES DA CONTRATADA</h4>
        </div>
        <p style="text-align: justify;">
            8.1. A CONTRATADA se responsabiliza por providenciar os instrumentos musicais, figurinos, acessórios e
            demais equipamentos necessários à execução da apresentação artística, assegurando que todos estejam em
            perfeitas condições de funcionamento;
            <br>8.2. Compete à CONTRATADA realizar previamente os testes de som e demais ajustes técnicos necessários,
            garantindo a qualidade da apresentação e a adequada operação dos equipamentos utilizados;
            <br>8.3. A CONTRATADA deverá comparecer ao local do evento com antecedência mínima de 30 (trinta) minutos
            em relação ao horário previsto para a apresentação, a fim de realizar a montagem de equipamentos, testes de
            som e alinhamento com a organização do evento;
            <br>8.4. A CONTRATADA será responsável pela montagem, operação e desmontagem dos seus equipamentos e
            instrumentos, devendo realizar tais atividades dentro dos prazos e orientações estabelecidos pela organização
            do evento, deixando o local em condições adequadas após o encerramento da apresentação;
            <br>8.5. Durante todo o período de permanência no local do evento, a CONTRATADA será responsável pela
            guarda e segurança de seus equipamentos e materiais, não cabendo à Administração Municipal qualquer
            responsabilidade por perdas, furtos ou danos;
            <br>8.6. A CONTRATADA deverá informar previamente à Administração Municipal suas exigências técnicas
            específicas para a apresentação (rider técnico), incluindo eventuais necessidades relacionadas a sonorização,
            iluminação, palco, camarins ou demais estruturas;
            <br>8.7. A CONTRATADA será integralmente responsável por quaisquer danos, incidentes ou prejuízos
            decorrentes da utilização de seus equipamentos ou da atuação de seus integrantes durante a apresentação,
            isentando a Administração Municipal de qualquer responsabilidade perante terceiros;
            <br>8.8. O serviço deverá ser prestado em conformidade com o presente contrato, com o Termo de Referência
            que integra o processo de inexigibilidade, bem como com as orientações da equipe organizadora do evento;
            <br>8.9. A CONTRATADA deve cumprir todas as obrigações constantes deste Contrato e de seus anexos,
            assumindo como exclusivamente seus os riscos e as despesas decorrentes da boa e perfeita execução do objeto,
            observando, ainda, as obrigações a seguir dispostas:
        </p>
        <ul style="text-align: justify; margin-top: 0;">
            <li>Manter a qualidade do som e respeitar o volume máximo permitido para eventos ao ar livre, conforme as diretrizes municipais;</li>
            <li>Seguir todas as orientações de segurança e adequar-se às normas estabelecidas pela organização do evento;</li>
            <li>Atender às determinações regulares emitidas pelo fiscal do contrato ou autoridade superior (art. 137, II) e prestar todo esclarecimento ou informação por eles solicitados;</li>
            <li>Responsabilizar-se pelos vícios e danos decorrentes da execução do objeto, de acordo com o Código de Defesa do Consumidor (Lei nº 8.078, de 1990), bem como por todo e qualquer dano causado à Administração ou terceiros, decorrentes de sua culpa ou dolo, não reduzindo essa responsabilidade a fiscalização ou o acompanhamento da execução contratual pela CONTRATANTE, que ficará autorizado a descontar dos pagamentos devidos ou da garantia, caso exigida no edital, o valor correspondente aos danos sofridos;</li>
            <li>Não contratar, durante a vigência do contrato, cônjuge, companheiro ou parente em linha reta, colateral ou por afinidade, até o terceiro grau, de dirigente da CONTRATANTE ou do fiscal ou gestor do contrato, nos termos do artigo 48, parágrafo único, da Lei nº 14.133, de 2021;</li>
            <li>Comunicar ao Fiscal do contrato, qualquer ocorrência anormal ou acidente que se verifique no local dos serviços;</li>
            <li>Prestar todo esclarecimento ou informação solicitada pela CONTRATANTE ou por seus prepostos, garantindo-lhes o acesso, a qualquer tempo, ao local dos trabalhos, bem como aos documentos relativos à execução do empreendimento;</li>
            <li>Conduzir os trabalhos com estrita observância às normas da legislação pertinente, cumprindo as determinações dos Poderes Públicos, mantendo sempre limpo o local dos serviços e nas melhores condições de segurança, higiene e disciplina;</li>
            <li>Não permitir a utilização de qualquer trabalho do menor de dezesseis anos, exceto na condição de aprendiz para os maiores de quatorze anos, nem permitir a utilização do trabalho do menor de dezoito anos em trabalho noturno, perigoso ou insalubre;</li>
            <li>Manter durante toda a vigência do contrato, em compatibilidade com as obrigações assumidas, todas as condições exigidas para qualificação na contratação direta;</li>
            <li>Cumprir, além dos postulados legais vigentes de âmbito federal, estadual ou municipal, as normas de segurança da CONTRATANTE.</li>
        </ul>
        <p style="text-align: justify;">
            8.10. O não cumprimento das obrigações assumidas poderá implicar na aplicação das penalidades previstas na
            Lei nº 14.133/2021 e no contrato, sem prejuízo da rescisão contratual.
        </p>

        {{-- ══════════════ CLÁUSULA NONA ══════════════ --}}
        <div style="margin-bottom: 20px;">
            <img src="{{ public_path('icons/descricao-necessidade.png') }}" width="30px" alt="">
            <h4 style="display: inline-block; margin: 0 0 0 10px; vertical-align: middle;">CLÁUSULA NONA - OBRIGAÇÕES DA CONTRATANTE</h4>
        </div>
        <p style="text-align: justify;">
            9.1. A CONTRATANTE obriga-se a:
            <br>9.1.1 Disponibilizar as condições técnicas, operacionais e logísticas necessárias para a plena execução da apresentação artística, conforme as especificações estabelecidas neste Termo de Referência e no contrato;
            <br>9.1.2. Prestar os esclarecimentos necessários à CONTRATADA quanto ao local, data e condições do evento, com a antecedência mínima necessária para viabilizar a preparação técnica da apresentação;
            <br>9.1.3. Garantir o acesso dos artistas e equipe técnica ao local da apresentação, inclusive em horários compatíveis com montagem, passagem de som e outras necessidades previstas pela CONTRATADA;
            <br>9.1.4. Fornecer a infraestrutura básica necessária à realização do evento, incluindo palco, sistema de sonorização, iluminação e fornecimento de energia elétrica, sendo que a instalação e operação do sistema de som e iluminação poderão ser executadas por fornecedor especializado contratado pela Administração;
            <br>9.1.5. Proceder ao pagamento dos valores contratados, conforme estabelecido no contrato e nas condições da proposta aprovada, desde que cumpridas as exigências de documentação fiscal e contratual;
            <br>9.1.6. Designar servidor responsável pelo acompanhamento da execução contratual e, se necessário, por atestar a realização da apresentação para fins de liberação do pagamento.
            <br>9.7. Informar à CONTRATADA sobre eventuais alterações no cronograma ou na estrutura do evento com antecedência razoável, desde que possível, buscando evitar prejuízos à execução do objeto contratado.
            <br>9.8. Fornecer por escrito às informações necessárias para o desenvolvimento dos serviços objeto do contrato;
            <br>9.9. Cientificar o órgão de representação judicial do município para adoção das medidas cabíveis quando do descumprimento das obrigações pela Contratada;
            <br>9.10. Caberá exclusivamente à CONTRATANTE a organização e liberação da realização do espetáculo junto a todos os órgãos públicos e entidades de classe, bem como junto às autoridades locais, inclusive o pagamento de todos e quaisquer impostos, taxas e contribuições de qualquer espécie ou natureza devidos, por força de Lei, a todos e quaisquer órgãos Municipais, Estaduais ou Federais, inclusive do ECAD (Escritório Central de Arrecadação de Direitos Autorais ou órgão similar, com antecedência de 05 (cinco) dias da data prevista para a realização da apresentação artística a que se refere o presente instrumento, bem como a obtenção de todas as licenças e alvarás necessários, inclusive junto ao Juizado de Menores, aos Órgãos de Censura de Diversões Públicas, das instituições arrecadadoras de direitos autorias, associadas ou independentes e a todas as demais entidades que possam interferir na realização ou no resultado da apresentação musical, e qualquer outra obrigação devida, seja de natureza fiscal, previdenciária, de direitos autorais ou qualquer outra, além de respeitar todas as normas de ordem pública para organização e realização do evento, em especial Polícia Militar e Corpo de Bombeiros bem como o pagamento de direitos autorais, se o caso;
            <br>9.11. Arcar com todas as despesas para a realização do evento, tais como, mas não limitadas a estas: palco, iluminação, sonorização, publicidade, segurança dos músicos, bem como do público presente, respeitando a orientação dos órgãos públicos, em especial Polícia Militar e Corpo de Bombeiros no tocante à razão número de seguranças x número de pessoas presentes, e espaço mínimo de segurança, entre o palco e o público, de 2 metros, isolado por disciplinadores ou equipamento equivalente que impeça o público de ficar muito próximo ao palco, sendo tal espaço reservado para seguranças do evento;
            <br>9.12. Informar com exatidão o estado do local onde o evento será realizado, respeitando a capacidade do mesmo, bem como as demais condições de segurança e saúde exigidas pelo Poder Público, todas as exigidas e que se fizerem necessárias, enviando fotografias ou vídeos;
            <br>9.13. Arcar com todo e qualquer prejuízo oriundo de demanda judicial, cuja causa seja o presente instrumento, seja de natureza indenizatória, trabalhista, tributária, previdenciária ou qualquer outra área do ramo do direito, isentando, em qualquer hipótese, a CONTRATADA de qualquer responsabilidade, garantindo-lhe o direito de regresso, bem como a devolução de toda e qualquer despesa havidas até a sua exclusão da lide ou término do processo, salvo se a causa for comprovadamente de responsabilidade da CONTRATADA, ou se tratar de caso fortuito ou força maior, nos termos da legislação civil;
        </p>

        {{-- ══════════════ CLÁUSULA DÉCIMA ══════════════ --}}
        <div style="margin-bottom: 20px;">
            <img src="{{ public_path('icons/descricao-necessidade.png') }}" width="30px" alt="">
            <h4 style="display: inline-block; margin: 0 0 0 10px; vertical-align: middle;">CLÁUSULA DÉCIMA - DAS PENALIDADES E SANÇÕES ADMINISTRATIVAS</h4>
        </div>
        <p style="text-align: justify;">
            10.1. Pela inexecução total ou parcial do objeto do CONTRATO, o Município poderá aplicar a CONTRATADA multa de até 5% (cinco por cento) do valor do contrato, sem prejuízo das demais penalidades previstas na Lei 14.133/21, inclusive responsabilização civil e penal na forma da Legislação específica;
            <br>10.2. Além da multa prevista ficam estabelecidas as penas de advertência, rescisão de contrato, declaração de inidoneidade e suspensão do direito de licitar e contratar com o MUNICÍPIO, que serão aplicadas em função da natureza e gravidade da falta cometida, garantida a ampla defesa.
            <br>10.3. O MUNICÍPIO reterá dos créditos decorrentes deste Contrato valores suficientes ao pagamento das multas aplicadas.
            <br>10.4. Nenhum pagamento será efetuado à CONTRATADA sem a quitação das multas aplicadas em definitivo.
            <br>10.5. Não será considerada inadimplente a CONTRATADA, ficando isenta do pagamento de qualquer multa ou indenização à CONTRATANTE, nas seguintes hipóteses:
            <br>a) Caso fortuito ou força maior, nos termos da legislação civil, aí compreendido eventos da natureza, tempestade com desmoronamento de barreira, falta de condição de pouso, black-out, ato de autoridade ou qualquer fato imprevisível e invencível capaz de impedir o comparecimento dos vocalistas, músicos, funcionários e equipamentos de propriedade da CONTRATADA.
        </p>

        {{-- ══════════════ CLÁUSULA DÉCIMA PRIMEIRA ══════════════ --}}
        <div style="margin-bottom: 20px;">
            <img src="{{ public_path('icons/descricao-necessidade.png') }}" width="30px" alt="">
            <h4 style="display: inline-block; margin: 0 0 0 10px; vertical-align: middle;">CLÁUSULA DÉCIMA PRIMEIRA - DA RESCISÃO</h4>
        </div>
        <p style="text-align: justify;">
            11.1. O presente Termo de Contrato poderá ser rescindido nas hipóteses previstas no art. 137 da Lei n° 14.133, de 2021, sem prejuízo das sanções aplicáveis, bem como amigavelmente, assegurados o contraditório e a ampla defesa, mediante distrato assinado pelas partes e confirmado por duas testemunhas. Nessa hipótese, não haverá qualquer ônus para as partes, ficando isentas quanto ao pagamento de indenização por danos materiais e morais eventualmente experimentados.
            <br>11.2. É admissível a fusão, cisão ou incorporação da contratada com/em outra pessoa jurídica, desde que sejam observados pela nova pessoa jurídica todos os requisitos de habilitação exigidos na licitação original; sejam mantidas as demais cláusulas e condições do contrato; não haja prejuízo à execução do objeto pactuado e haja a anuência expressa da Administração à continuidade do contrato;
            <br>11.3. Os casos de rescisão contratual serão formalmente motivados, assegurando-se à CONTRATADA o direito à prévia e ampla defesa;
        </p>

        {{-- ══════════════ CLÁUSULA DÉCIMA SEGUNDA ══════════════ --}}
        <div style="margin-bottom: 20px;">
            <img src="{{ public_path('icons/descricao-necessidade.png') }}" width="30px" alt="">
            <h4 style="display: inline-block; margin: 0 0 0 10px; vertical-align: middle;">CLÁUSULA DÉCIMA SEGUNDA - DOS CASOS OMISSOS</h4>
        </div>
        <p style="text-align: justify;">
            12.1. Os casos omissos serão decididos pela CONTRATANTE, segundo as disposições contidas na Lei n° 14.133, de 2021, e demais normas federais de licitações e contratos administrativos e, subsidiariamente, segundo as disposições contidas na Lei n° 8.078, de 1990 - Código de Defesa do Consumidor - e normas e princípios gerais dos contratos.
            <br>12.2. Na hipótese de reagendamento por cancelamento da apresentação artística, objeto deste contrato, em virtude de força maior e/ou caso fortuito, as despesas concernentes à logística do artista e equipe, necessárias para a execução do objeto do contrato em nova data a ser designada por ambas as partes, serão de responsabilidade do CONTRATANTE, haja vista sua qualidade de promotor e produtor do evento.
            <br>12.2.1. Caso as partes não optem por designar uma nova data para a apresentação artística, na hipótese prevista no item 12.2., a CONTRATADA compromete-se a devolver os valores já pagos pelo CONTRATANTE em tempo hábil, retendo apenas os valores referentes à logística já contratados e pagos.
        </p>

        {{-- ══════════════ CLÁUSULA DÉCIMA TERCEIRA ══════════════ --}}
        <div style="margin-bottom: 20px;">
            <img src="{{ public_path('icons/descricao-necessidade.png') }}" width="30px" alt="">
            <h4 style="display: inline-block; margin: 0 0 0 10px; vertical-align: middle;">CLÁUSULA DÉCIMA TERCEIRA - DA FUNDAMENTAÇÃO LEGAL E PUBLICAÇÃO</h4>
        </div>
        <p style="text-align: justify;">
            13.1. O presente Contrato tem embasamento legal no artigo 74, inciso II da Lei 14.133, de 2021.
            <br>13.2. É de responsabilidade da CONTRATANTE a publicação legal do instrumento.
        </p>

        {{-- ══════════════ CLÁUSULA DÉCIMA QUARTA ══════════════ --}}
        <div style="margin-bottom: 20px;">
            <img src="{{ public_path('icons/descricao-necessidade.png') }}" width="30px" alt="">
            <h4 style="display: inline-block; margin: 0 0 0 10px; vertical-align: middle;">CLÁUSULA DÉCIMA QUARTA – DAS DISPOSIÇÕES FINAIS</h4>
        </div>
        <p style="text-align: justify;">
            14.1. As partes deverão cumprir a Lei nº 13.709, de 14 de agosto de 2018 (LGPD), quanto a todos os dados pessoais a que tenham acesso em razão do certame ou do contrato administrativo que eventualmente venha a ser firmado, a partir da apresentação da proposta no procedimento de contratação, independentemente de declaração ou de aceitação expressa.
            <br>14.2. Os dados obtidos somente poderão ser utilizados para as finalidades que justificaram seu acesso e de acordo com a boa-fé e com os princípios do art. 6º da LGPD.
        </p>
        <ul style="text-align: justify; margin-top: 0;">
            <li>É vedado o compartilhamento com terceiros dos dados obtidos fora das hipóteses permitidas em Lei.</li>
            <li>Terminado o tratamento dos dados nos termos do art. 15 da LGPD, é dever da CONTRATADA eliminá-los, com exceção das hipóteses do art. 16 da LGPD, incluindo aquelas em que houver necessidade de guarda de documentação para fins de comprovação do cumprimento de obrigações legais ou contratuais e somente enquanto não prescritas essas obrigações.</li>
            <li>É dever da CONTRATADA orientar e treinar seus empregados sobre os deveres, requisitos e responsabilidades decorrentes da LGPD.</li>
        </ul>

        {{-- ══════════════ CLÁUSULA DÉCIMA QUINTA ══════════════ --}}
        <div style="margin-bottom: 20px;">
            <img src="{{ public_path('icons/descricao-necessidade.png') }}" width="30px" alt="">
            <h4 style="display: inline-block; margin: 0 0 0 10px; vertical-align: middle;">CLÁUSULA DÉCIMA QUINTA - DO FORO</h4>
        </div>
        <p style="text-align: justify;">
            15.1. Fica eleito o foro da Comarca de {{ $processo->contrato->comarca }} como único e competente para dirimir quaisquer demandas do presente contrato, por mais privilegiado que outro possa ser.
            <br>15.2. E por estarem justos e contratados, firmam o presente em 02 (duas) vias de igual teor e forma para que produzam os efeitos legais.
        </p>

        {{-- Bloco de data e assinatura --}}
        <div class="footer-signature">
            {{ $processo->prefeitura->cidade }},
            {{ \Carbon\Carbon::parse($dataSelecionada)->translatedFormat('d \d\e F \d\e Y') }}
        </div>

        <div style="margin-top: 40px; text-align: center;">
            <div class="signature-block" style="display: inline-block; margin: 0 40px;">
                ___________________________________<br>
                <p style="line-height: 1.2;">
                    {{ $primeiroAssinante['responsavel'] ?? $processo->detalhe->responsavel }}<br>
                    PREFEITO MUNICIPAL<br>
                    CONTRATANTE
                </p>
            </div>
        </div>

        <div style="margin-top: 40px; text-align: center;">
            <div class="signature-block" style="display: inline-block; margin: 0 40px;">
                ___________________________________<br>
                <p style="line-height: 1.2;">
                    {{ $processo->detalhe->representante_legal_empresa }}<br>
                    REPRESENTANTE<br>
                    CONTRATADO
                </p>
            </div>
        </div>

        <div style="margin-top: 40px;">
            TESTEMUNHAS<br><br>

            ___________________________________<br>
            CPF:<br><br>

            ___________________________________<br>
            CPF:
        </div>

        {{-- QUEBRA DE PÁGINA --}}
        <div class="page-break"></div>

        {{-- ══════════════ EXTRATO DO CONTRATO ══════════════ --}}
        <div>
            <table style="width:100%; border-collapse:collapse; font-size:10px; margin-bottom:20px; " border="1">

                <tr>
                    <td colspan="2" style="padding:8px; text-align:center; font-weight:bold;">
                        EXTRATO DO CONTRATO Nº {{ $processo->contrato->numero_extrato }}<br>
                        PROCESSO ADMINISTRATIVO Nº {{ $processo->numero_processo }}<br>
                        MODALIDADE: INEXIGIBILIDADE DE LICITAÇÃO Nº {{ $processo->numero_procedimento }}
                    </td>
                </tr>

                <tr>
                    <td style="padding:6px; width:30%; font-weight:bold;">OBJETO:</td>
                    <td style="padding:6px;">{!! strip_tags($processo->objeto) !!}</td>
                </tr>

                <tr>
                    <td style="padding:6px; font-weight:bold;">CONTRATANTE:</td>
                    <td style="padding:6px;">{{ $processo->prefeitura->nome }}</td>
                </tr>

                <tr>
                    <td style="padding:6px; font-weight:bold;">CONTRATADO:</td>
                    <td style="padding:6px;">{{ $processo->detalhe->razao_social }}</td>
                </tr>

                <tr>
                    <td style="padding:6px; font-weight:bold;">CNPJ (CONTRATADO):</td>
                    <td style="padding:6px;">{{ $processo->detalhe->cnpj_empresa_vencedora }}</td>
                </tr>

                <tr>
                    <td style="padding:6px; font-weight:bold;">VALOR:</td>
                    <td style="padding:6px;">R$ {{ $detalhe->valor_estimado }}</td>
                </tr>

                <tr>
                    <td style="padding:6px; font-weight:bold;">FONTE DOS RECURSOS:</td>
                    <td style="padding:6px;">{!! strip_tags($processo->detalhe->dotacao_orcamentaria) !!}</td>
                </tr>

                <tr>
                    <td style="padding:6px; font-weight:bold;">FUNDAMENTAÇÃO LEGAL:</td>
                    <td style="padding:6px; text-align:justify;">
                        Será regida pelas normas fixadas nesta Inexigibilidade de Licitação, e pelo artigo 74, inciso II
                        da Lei 14.133/21, de 1 de abril de 2021, e legislação posterior, que o suplementam no que for omisso.
                    </td>
                </tr>

                <tr>
                    <td style="padding:6px; font-weight:bold;">ASSINATURA (CONTRATANTE):</td>
                    <td style="padding:6px;">{{ $primeiroAssinante['responsavel'] ?? $processo->detalhe->responsavel }}</td>
                </tr>

                <tr>
                    <td style="padding:6px; font-weight:bold;">ASSINATURA (CONTRATADO):</td>
                    <td style="padding:6px;">{{ $processo->detalhe->representante_legal_empresa }}</td>
                </tr>

                <tr>
                    <td style="padding:6px; font-weight:bold;">DATA DA ASSINATURA:</td>
                    <td style="padding:6px;">
                        {{ \Carbon\Carbon::parse($processo->contrato->data_assinatura_contrato)->translatedFormat('d \d\e F \d\e Y') }}
                    </td>
                </tr>

            </table>
        </div>
    </div>

</body>

</html>
