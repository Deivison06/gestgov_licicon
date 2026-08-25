# **📝 Observações**

ago. 19, 2026

## **Reunião em 19 de ago. de 2026 às 20:10 GMT-03:00**

Registros da reunião [Transcrição](https://docs.google.com/document/d/1EBt8NUadaxJK6nvPUb9vCPistNb-WDrblOA8Je2pRBg/edit?usp=drive_web&tab=t.qth803eesd6w) 

### **Resumo**

Reunião definiu padronização de fluxos para aditivos contratuais integrando automação de dados com nova interface visual.

**Integração de interface aditivos**  
O sistema integrará controles de aditivos diretamente aos contratos existentes para evitar poluição visual. A interface exibirá inicialmente apenas incidentes contratuais já finalizados.

**Automação e cálculos aditivos**  
O sistema automatizará cálculos de valor para compras e serviços enquanto obras exigirão importação manual de dados. Alterações refletirão automaticamente no sistema de almoxarifado.

**Padronização documental e dados**  
A documentação de 12 páginas adotará estrutura unificada com geração sequencial integrada. A entrada de dados críticos no início do processo garante a precisão dos cálculos.

### **Próximas etapas**

- [ ] \[Deivison Santos\] Implementar Aba Aditivos: Implementar a aba de aditivos na interface de fiscalização, permitindo que o registro de novos incidentes contratuais seja feito tanto pela tela de contratos quanto pela de processos.

- [ ] \[Deivison Santos\] Atualizar Seletor: Adicionar todos os tipos de incidentes ao seletor de opções, mantendo ocultos no momento apenas os que ainda não estão prontos.

- [ ] \[Fred de Oliveira Roldão\] Enviar Modelo: Enviar o arquivo com o modelo de aditivos e as marcações das partes que variam para referência do desenvolvedor.

- [ ] \[Deivison Santos\] Criar Template Único: Desenvolver um arquivo único para os modelos de aditivo que suporte as diferentes situações, evitando a criação de múltiplos PDFs distintos.

- [ ] \[Deivison Santos\] Configurar Campos: Criar campos na nova página de aditivos para preenchimento de justificativa, porcentagem de alteração de valor, quantidade de meses e anexação da solicitação do aditivo.

- [ ] \[Deivison Santos\] Automatizar Parecer: Configurar o sistema para marcar automaticamente os checkboxes do parecer jurídico conforme o tipo de aditivo selecionado.

### **Detalhes**

* **Adição de Interface para Aditivos**: Fred de Oliveira Roldão solicitou a inclusão de um botão ou aba de "novo incidente contratual" tanto na visualização de contratos quanto na aba de processos. O objetivo é permitir que os aditivos sejam gerenciados de forma centralizada e visível, seguindo o padrão já existente nas abas de fiscalização ([00:00:19](#00:00:19)).

* **Configuração de Incidentes Contratuais**: Ficou decidido que a lista de incidentes contratuais será organizada de modo que apenas as opções já finalizadas (identificadas como verdes) fiquem visíveis inicialmente para o usuário. Deivison Santos planeja adicionar todas as opções no banco de dados, mas manterá as demais ocultas para evitar confusão operacional, com a possibilidade de torná-las visíveis futuramente conforme necessário ([00:02:07](#00:02:07)).

* **Tipos de Aditivos e Cálculos**: A discussão abordou a diferenciação entre aditivos de prazo e aditivos de valor/quantitativo. Para casos de aumento de quantitativo em contratos de compra e serviço, o sistema deve ser configurado para permitir a entrada de uma porcentagem, calculando automaticamente os novos valores com base na planilha interna do sistema, permitindo a conferência do montante total antes da finalização ([00:02:48](#00:02:48)).

* **Impacto da Sincronização no Almoxarifado**: Fred de Oliveira Roldão ressaltou que as alterações de quantitativos efetuadas via aditivo devem refletir automaticamente em três frentes: na aba de contratos, nos controles internos e no sistema do almoxarifado, garantindo que o setor responsável pela compra esteja atualizado sobre a nova capacidade de aquisição ([00:04:29](#00:04:29)).

* **Fluxo para Aditivos de Obras e Serviços**: Foi estabelecida a distinção técnica entre aditivos de obras e aditivos de compra/serviço. Em obras, não haverá o uso da planilha interna do sistema, sendo necessário importar um PDF com a planilha orçamentária da empresa e inserir manualmente a porcentagem e o valor do aditivo. Para compras e serviços, o sistema utilizará a planilha já integrada para automatizar os cálculos ([00:05:26](#00:05:26)).

* **Padronização da Documentação de Aditivos**: Deivison Santos e Fred de Oliveira Roldão discutiram a estrutura de 12 páginas dos documentos de aditivo. Decidiram evitar a criação de múltiplos arquivos distintos. Deivison Santos revisará os documentos compartilhados por Fred de Oliveira Roldão para criar uma estrutura unificada e compacta, mantendo o texto original e apenas ajustando o que for necessário conforme a situação ([00:07:19](#00:07:19)).

* **Geração Sequencial de Documentos**: O processo de geração de documentos deve seguir uma ordem lógica para evitar redundância. O fluxo planejado inclui o preenchimento da justificativa inicial, seguido da geração automática de documentos como o despacho para a procuradoria, o parecer jurídico, a autorização do prefeito e o termo aditivo final, com campos de marcação automática no sistema conforme o tipo de aditivo (valor ou prazo) ([00:11:22](#00:11:22)).

* **Entrada de Dados e Parâmetros Iniciais**: Foi definido que, para assegurar a correta execução do aditivo, os dados críticos — como número de meses ou dias (para prazo) e porcentagens (para valor) — devem ser informados pelo usuário logo no início da criação do processo. Isso permitirá que o sistema puxe automaticamente os dados do contrato original e execute os cálculos necessários sem erro ([00:14:53](#00:14:53)).

* **Visibilidade e Armazenamento dos Aditivos**: Para evitar a poluição da interface, Fred de Oliveira Roldão e Deivison Santos concordaram que não criarão uma aba de alto nível exclusiva para aditivos. Em vez disso, os aditivos serão vinculados diretamente aos processos e contratos correspondentes, permitindo que sejam encontrados ao pesquisar pelo nome da empresa ou pelo número do contrato dentro das pastas existentes ([00:17:09](#00:17:09)).

*Revise as anotações do Gemini para checar se estão corretas. [Confira dicas e saiba como o Gemini faz anotações](https://support.google.com/meet/answer/14754931)*

*Como está a qualidade de **destas observações?** [Responda a uma breve pesquisa](https://google.qualtrics.com/jfe/form/SV_5bXzKQfylMIhSXc?confid=owNjEcm2XlW5h4UNJsolDxITOAIIigIgABgFCA&detailLevel=standard&hasImages=False&entryPoint=footerMain&isGoogler=False) para nos dar seu feedback, incluindo o quanto as observações foram úteis para o que você precisa.*

# **📖 Transcrição**

ago. 19, 2026

## **Reunião em 19 de ago. de 2026 às 20:10 GMT-03:00 \- Transcrição**

### **00:00:19** {#00:00:19}

**Fred de Oliveira Roldão:** E aí, meu amigo, como vai?

**Deivison Santos:** Boa noite, meu amigo.

**Fred de Oliveira Roldão:** Tudo bom?

**Deivison Santos:** Show de bola.

**Fred de Oliveira Roldão:** Tranquilo. Bora lá terminar aqui. Eu só vou dormir.

**Deivison Santos:** É o

**Fred de Oliveira Roldão:** Hum. Não fisioterapia ali,

**Deivison Santos:** S.

**Fred de Oliveira Roldão:** tô só bagaço. Bora lá. Eh, deixa deixa eu abrir bem aqui o que fecha aqui. Pronto. Como é que funciona? Eh, eh, vamos lá. Eh, basicamente em qualquer contrato aqui, eh, contrato, inclusive, o Wesley fez uma atualização nova que aparece aqui. Aí vai ser, eu queria que quando quando fizesse o aditivo já aparecesse aqui em cima disso aqui, parecido, entendeu? Tipo uma aba dessa, só que aqui em cima, ó.

**Deivison Santos:** acima desse acompanhamento da execução,

**Fred de Oliveira Roldão:** de fiscalização. Isso.

**Deivison Santos:** né?

**Fred de Oliveira Roldão:** Isso aqui é dos fiscal que tem a aba de fiscalização aqui.

### **00:01:21**

**Deivison Santos:** Sim,

**Fred de Oliveira Roldão:** Eu pedi para ele sincronizar de lá de com carro.

**Deivison Santos:** sim,

**Fred de Oliveira Roldão:** Aí eu eu quero eu quero que seja feito tanto por aqui quanto que pela aba processos.

**Deivison Santos:** sim.

**Fred de Oliveira Roldão:** Aqui você entra aqui na aba de

**Deivison Santos:** Tanto pelo contrato manual quanto pelo pela aba de processo,

**Fred de Oliveira Roldão:** contrato. Isso.

**Deivison Santos:** né?

**Fred de Oliveira Roldão:** Isso. Aí a gente entra por aqui. Deixa eu pegar um que tá finalizado aqui que tem um contrato. É esse aqui. Você entre aqui. Aí. Daí tem aqui em cima aqui o tanto lá é novo incidente, novo eh que o nome tá aqui meus novo incidente. Registrar novo incidente contratual, entendeu? Aí tem aqui,

**Deivison Santos:** Aí aí ao lado desse botão aí fica mesmo ao lado do

**Fred de Oliveira Roldão:** pode falar.

**Deivison Santos:** botão de voltar processo.

**Fred de Oliveira Roldão:** É, é, pode ser aqui em cima, que nem é feito geralmente aqui nos contratos, que tem uns botãozinhos aqui em cima.

### **00:02:07** {#00:02:07}

**Deivison Santos:** Certo,

**Fred de Oliveira Roldão:** Vamos,

**Deivison Santos:** certo.

**Fred de Oliveira Roldão:** vamos aqui pelos contratos que é mais fácil de visualizar.

**Deivison Santos:** Pronto,

**Fred de Oliveira Roldão:** Aí aquele novo novo incidente contratual.

**Deivison Santos:** pronto.

**Fred de Oliveira Roldão:** Aí a gente registrasse esse novo incidente que pode ser qualquer um desses aqui, entendeu? Aí eh inicialmente a gente vai incluir só esses verdes aqui, que é os que já estão prontos. Esses outros aqui para baixo eu vou te mandar depois,

**Deivison Santos:** S.

**Fred de Oliveira Roldão:** só que eu já deixei registrado aqui para tu saber que mais para frente a gente vai incluir eles,

**Deivison Santos:** Eu eu vou fazer o seguinte,

**Fred de Oliveira Roldão:** tá bom?

**Deivison Santos:** eu vou adicionar todos lá no select, mas aí não vai ter como gerar os outros, né?

**Fred de Oliveira Roldão:** Pronto,

**Deivison Santos:** Eu,

**Fred de Oliveira Roldão:** pode ser.

**Deivison Santos:** na verdade eu vou fazer o seguinte, eu vou eu vou colocar todos, mas eu vou deixar mostrando só os verdes.

**Fred de Oliveira Roldão:** Deixa, deixa ocultado. Pronto. É, é melhor,

### **00:02:48** {#00:02:48}

**Deivison Santos:** Isso,

**Fred de Oliveira Roldão:** é melhor porque aí não, o pessoal não se confunde.

**Deivison Santos:** porque aí depois eu só tira deixa ele mostro,

**Fred de Oliveira Roldão:** Espera, só isso.

**Deivison Santos:** né, ele e boto só Oh. o os PDF, né, que é ser gerado

**Fred de Oliveira Roldão:** Isso, isso, exatamente isso fica melhor dessa forma,

**Deivison Santos:** melhor.

**Fred de Oliveira Roldão:** porque aí já fica preparado já para mais para frente, que é o que a gente tinha que ser feito desde o começo.

**Deivison Santos:** Да.

**Fred de Oliveira Roldão:** Aí, eh, eu, eh, como eu te expliquei ontem, eh, vai ter aqui os tipos de aditivo, né? Aí tem o aditivo para prazo que a gente vai vai mexer, vai mudar só o prazo do contrato de valor que a gente vai alterar a quantidade do valor. Essa alteração na quantidade do valor que não te expliquei ontem é uma alteração do quantitativo. Então a gente vai a gente vai alterar a quantidade dos itens de um contrato. Então quando a gente clicasse lá, vamos supor, vai ter a opção aqui, eh,

**Deivison Santos:** เ

**Fred de Oliveira Roldão:** acréscimo do quantitativo para para compra e serviço, né?

### **00:03:45**

**Fred de Oliveira Roldão:** Aí a gente já seleciona o contrato, já vai aparecer que nem que nem aparece lá para para as atas lá e a gente quer assinar, aditivar 25% do contrato, a gente digita 25 lá% e o próprio e o sistema já com com a planilha que vai estar dentro do sistema, o sistema já vai calcular eh quanto vai dar essas quantidades. embaixo, ele vai dizer o valor, o valor que o valor total que vai dar o aditivo, que a gente vai ter a possibilidade de conferir se o valor vai tá dentro do negócio, dentro da

**Deivison Santos:** Certo. No caso,

**Fred de Oliveira Roldão:** quantidade.

**Deivison Santos:** no caso a planilha que tu fala que tá dentro do sistema, é a planilha do contrato, né?

**Fred de Oliveira Roldão:** É aparenta, com os itens,

**Deivison Santos:** Certo.

**Fred de Oliveira Roldão:** por exemplo,

**Deivison Santos:** Certo.

**Fred de Oliveira Roldão:** quando a gente vai nos contratos.

**Deivison Santos:** Aqui aparece lá no contrato, né?

**Fred de Oliveira Roldão:** Isso, isso. Aquele,

**Deivison Santos:** Eu sei.

**Fred de Oliveira Roldão:** aquele quantitativo lá aí que também que que aparece lá na numa mxarifada,

### **00:04:29** {#00:04:29}

**Deivison Santos:** Sei.

**Fred de Oliveira Roldão:** aquela mesma aquele mesmo jeitinho lá, umas caixinhas e vai puxar os itens.

**Deivison Santos:** Show.

**Fred de Oliveira Roldão:** Aí quando essa alteração for feita, o sistema já vai ter que puxar, quando a gente fizer o finalizar o aditivo, né? Aí o sistema já vai ter que alterar, vai ter que vai ter que alterar as quantidades dentro dos dentro do contrato, porque, por exemplo, vai influenciar tanto noxarifado lá que, por exemplo, se a gente fizer aditivo de 25%,

**Deivison Santos:** Uhum.

**Fred de Oliveira Roldão:** vai aumentar 25% lá que que a prefeitura pode comprar, entendeu? Então tem que alterar tanto no nosso controle interno que na aba de contratos e do processo,

**Deivison Santos:** Certo.

**Fred de Oliveira Roldão:** quanto lá no setor de almocharifado lá pro pessoal fazer a compra, entendeu? Então o os processos em si,

**Deivison Santos:** Sério?

**Fred de Oliveira Roldão:** eles são bem parecidos de aditivo para obra e para compra. A diferença do caso da obra é que não vai ter essa questão da planilha. O aditivo da obra, a a empresa vai enviar uma planilha orçamentária.

### **00:05:26** {#00:05:26}

**Fred de Oliveira Roldão:** Deixa eu ver se tem uma um exemplo aqui.

**Deivison Santos:** Угу.

**Fred de Oliveira Roldão:** Tem uma bem aqui, ó. Deixa eu pegar faço bem aqui para só para tu entender como é que é. A empresa vai enviar uma planilha tipo essa daqui, ó, que é em PDF, já é feito por ela, que vai ter aqui os quantitativos aqui da obra, entendeu? Então, a gente vai só exportar o PDF e vai e vai digitar manuscrito mesmo pro pro sistema entender quantos por centá na proposta vai ter vai tá dizendo que em caso de obra é até 50%. Já compro. serviço até 25%. Então vamos supor que a obra é 45%, a gente vai importar o PDF e vai dizer pro sistema que é 45% e vai digitar o valor lá do da obra, entendeu? Que é do aditivo. Porque no caso de compra e serviço, como a planilha já vai estar dentro do sistema, o próprio sistema já vai saber quanto é que é o valor do aditivo, entendeu? Aí no aí no caso da obra, como o sistema também já vai ter o valor total, até pode até deixar, a gente vai digitar a porcentagem lá e o sistema já pode até calcular quantos,

### **00:06:19**

**Deivison Santos:** S

**Fred de Oliveira Roldão:** qual o valor do aditivo, porque aí diminui até o os os campos de digitação, né? Vai diminuir a questão do erro. Aí você digita aqui a caixa aqui, coloca, escolhe o tipo de aditivo. Para caso de obra, eh, tem obra ou compra e serviço. Aí para cada para cada para cada caso desse tem um ou dois ou dois juntos, que nem eu te expliquei ontem, a gente tanto vai poder fazer só prazo ou só ou só ou só valor ou ou prazo e valor, os dois juntos, entendeu?

**Deivison Santos:** Certo,

**Fred de Oliveira Roldão:** Então, por isso que tem esse tanto de opções aqui que tem eh

**Deivison Santos:** certo.

**Fred de Oliveira Roldão:** prazo e valor junto ou só prazo ou só valor, entendeu? Aí quando Oi,

**Deivison Santos:** Eu acho que é, tá?

**Fred de Oliveira Roldão:** pode falar,

**Deivison Santos:** Eu acho que isso isso aí tem quantas páginas? Mais ou menos aí, que eu ainda não dei uma olhada.

**Fred de Oliveira Roldão:** rapaz, moço, tem 12 páginas só.

**Deivison Santos:** Eu acho que tem 12, né?

### **00:07:19** {#00:07:19}

**Fred de Oliveira Roldão:** É, é bem pequenininho o

**Deivison Santos:** Eu vou ver se eu eu vou eu vou ver se eu consigo criar tipo um PDF

**Fred de Oliveira Roldão:** processo.

**Deivison Santos:** só dá uma compactação lá, fazer um um jeito bom para não ter que ficar criando vários tipos de PDF, sabe? Aqui aí fazer um só.

**Fred de Oliveira Roldão:** Sei, sei, sei.

**Deivison Santos:** Mas aí aqui eu aqui mesmo,

**Fred de Oliveira Roldão:** Então,

**Deivison Santos:** eu não vou mudar nada do texto não.

**Fred de Oliveira Roldão:** então, pois é. Pois é.

**Deivison Santos:** Não vou mudar nada do texto

**Fred de Oliveira Roldão:** Então, então é de tinha tinha essa opção.

**Deivison Santos:** não.

**Fred de Oliveira Roldão:** Eu tenho eu tenho um arquivo aqui dessa forma que tem lá ou ou para uma situação ou para outra ou para outra, entendeu? Eu separei dessa forma para ficar melhor para amigo entender,

**Deivison Santos:** Não, não.

**Fred de Oliveira Roldão:** mas eu tenho uma opção aqui que, por exemplo, fica eh só tem um só tem um para cada, só tem um para obra e um para compra e serviço. Aí lá dentro do arquivo tá dizendo para para esse caso essa daqui, para esse caso essa daqui, para esse caso essa daqui.

### **00:08:12**

**Fred de Oliveira Roldão:** Aí se o amigo preferir eu posso mandar ele, porque no caso ficaria um arquivo de importação e o amigo só escolheria qual colocaria,

**Deivison Santos:** Pronto, eu tô Pronto.

**Fred de Oliveira Roldão:** né?

**Deivison Santos:** Tu me manda esse arquivo aí. Aqui é eu já tenho esses outros aí, né? Eu já tenho esses aí.

**Fred de Oliveira Roldão:** Pronto.

**Deivison Santos:** Eu consigo dar uma olhada tanto em um quanto em outro para eu conseguir entender melhor e e crio dessa dessa forma aí para não ter criar vários artigos aqui, sabe?

**Fred de Oliveira Roldão:** Ei, deixa eu só ver bem aqui que eu acho que talvez eu ainda tenha eles bem aqui.

**Deivison Santos:** Não,

**Fred de Oliveira Roldão:** Eu já fui a mostro.

**Deivison Santos:** mas também se não tiver bicho não.

**Fred de Oliveira Roldão:** Não, João Vit tem o o que trabalha com a gente.

**Deivison Santos:** M.

**Fred de Oliveira Roldão:** É porque eu separei eles assim para ficar mais fácil do amigo entender. Mas assim, o que vai mudar basicamente é só esse texto aqui. Por exemplo, deixa eu botar um do lado do outro aqui pro amigo ver, ó. Esse aqui é, deixa eu fechar esse aqui, que isso aqui é prazo serviço.

### **00:09:04**

**Fred de Oliveira Roldão:** Deixa eu botar valor e prazo. Ó, teoricamente eles são a mesma coisa. Você vai mudar a capa, né? Aí aqui vai mudar só esse texto aqui, ó, que vai dizer uma coisa e aqui vai dizer outra. Tá vendo? Vai mudar, vai mudar só esses detalhes. Vai mudar o parecer jurídico. Vai mudar, ó, parecer jurídico aqui, ó. É a mesma coisa igual. Só muda esse que tá,

**Deivison Santos:** A mesma estrutura, né?

**Fred de Oliveira Roldão:** é, só muda esse aqui que tá em vermelho,

**Deivison Santos:** Sei.

**Fred de Oliveira Roldão:** ó, que aqui é valor e aqui é prazo,

**Deivison Santos:** Uhum.

**Fred de Oliveira Roldão:** entendeu?

**Deivison Santos:** Tô

**Fred de Oliveira Roldão:** Aí o resto, o resto vai mudar o quê?

**Deivison Santos:** entendendo

**Fred de Oliveira Roldão:** Vai mudar isso aqui do do objeto do objeto aqui, ó. Aqui nesse aqui, nesse aqui que é pr pra quantidade, tem a tem a planilha e aqui o objeto.

**Deivison Santos:** eu sei.

### **00:09:45**

**Fred de Oliveira Roldão:** Aí nesse aqui que é só prazo, só tem a planilha do prazo, entendeu? Então assim, o que que eu posso dizer pro amigo? seu amigo. Acho que fica até mais fácil, fica, acho que fica mais fácil seu amigo pegar um desse para um caso específico e eh vendo vendo o que muda, né? Lendo, vendo o que muda e só alterar o texto,

**Deivison Santos:** Não é coisa desse.

**Fred de Oliveira Roldão:** entendeu?

**Deivison Santos:** Eu acho que dessa forma que eu falei aqui dá dá certo. Vou tentar criar tudo no arquivo,

**Fred de Oliveira Roldão:** É porque se eu mandar o ou é porque se eu mandar o ou o inicial lá que tinha vai

**Deivison Santos:** certo?

**Fred de Oliveira Roldão:** ficar confuso. Eu eu me confundi para ajeitar isso aqui porque

**Deivison Santos:** Não, não. Então,

**Fred de Oliveira Roldão:** o não é porque o outro

**Deivison Santos:** deve dar certo mesmo desse jeito. Tu acha que tá tá confuso. Deixa eu só com esse aqui

**Fred de Oliveira Roldão:** lá é o outro lá tá confuso.

**Deivison Santos:** mesmo.

**Fred de Oliveira Roldão:** Mas eu vou fazer o seguinte, ó.

### **00:10:29**

**Fred de Oliveira Roldão:** Eu vou marcar bem aqui o que que vai mudar, ó.

**Deivison Santos:** Não, mas aí dá dá para mim ter dá para mim ter noção.

**Fred de Oliveira Roldão:** Mudou. Eu vou marcar só em um só pro amigo ter, só pro amigo, caso amigo fique em dúvida, eu vou deixar marcado aqui só em um para para mostrar aqui. Vixe Maria, só para ver mostrar o que muda aqui. É isso aí. Só muda isso aqui. Aí só muda esse do objeto aqui. E aqui só muda isso aqui. Só isso que muda de um pro outro.

**Deivison Santos:** Pronto.

**Fred de Oliveira Roldão:** Aí esse aqui é o termo adjetivo de compra e a capa que onde uma diz que é uma coisa,

**Deivison Santos:** E a capa, né?

**Fred de Oliveira Roldão:** outro diz que é outra. Só isso, entendeu? Aí aí outra coisa para nós fazer aqui para agilizar o lado do amigo aí que eu ia até fazer aqui que era os campos porque ontem nós conversamos de gerar tudo junto, né? para facilitar,

**Deivison Santos:** Sim,

### **00:11:22** {#00:11:22}

**Fred de Oliveira Roldão:** para não ficar aquele, para não criar outra página nova, para ficar gerar um por um,

**Deivison Santos:** sei.

**Fred de Oliveira Roldão:** pra gente gerar tudo de uma vez. Então, o que que eu tava pensando aqui? Isso dar uma olhada aqui para ver o que que ele pode puxar. Vai puxar isso aqui, isso aqui puxa de lá, isso aqui ele já puxa do sistema. Isso aqui puxa do sistema. Primeira coisa é a justificativa eh para a solicitação de aditivo. É o primeiro campo que a gente tem que colocar lá para preencher.

**Deivison Santos:** E pode ser com I,

**Fred de Oliveira Roldão:** O resto ele puxa de lá.

**Deivison Santos:** né?

**Fred de Oliveira Roldão:** Pode. É até bom que tem aí. Outra questão que a gente vai ter com problema É só, é, é, eu tava pensando em colocar separado por causa das datas, né? Porque o aditivo, como ele é um mini processo, ele tem que correr em uns dias ali, né? Aí o maior problema era a questão da data, né?

### **00:12:11**

**Fred de Oliveira Roldão:** Porque tem data aqui, tem data no parecer e tem data no tem data nessa.

**Deivison Santos:** Mas mas cada mas cada data dessa aí é é é diferente

**Fred de Oliveira Roldão:** Pode falar, é diferente. Esse que era o problema que eu tava em dúvida, porque, por exemplo, se o aditivo ele é feito hoje,

**Deivison Santos:** Entendi.

**Fred de Oliveira Roldão:** aí teria teria que começar lá pelo dia 12\. Aí cada documento seria uma data,

**Deivison Santos:** Entendi. Aí não, eu penso aqui um jeito para ficar bom aí para vocês.

**Fred de Oliveira Roldão:** entendeu?

**Deivison Santos:** Vou vou tentar criar uma tipo uma página daquela mesmo. Melhor porque é

**Fred de Oliveira Roldão:** É, acho que fica, acho que fica melhor. Pois é. Mas então, então vou colocar aqui para solicitação.

**Deivison Santos:** melhor.

**Fred de Oliveira Roldão:** Eh, pronto, é solicitação da aditiv. precisa preencher isso aqui pro pr para aí. Aí vamos lá, vamos organizar até o amigo tá gravando, ele não tá pronto.

**Deivison Santos:** Tô gravando agora.

**Fred de Oliveira Roldão:** Pronto.

### **00:13:08**

**Fred de Oliveira Roldão:** Já que a gente vai é, já que a gente vai criar uma página daquela que nem do processo, então é bom até já vai ter aí, eu tenho que explicar essa parte porque vai vir, vai ter que organizar, né, que vai sair em cada. Então, por exemplo, esse documento aqui, essa solicitação aqui, ela vai vir acompanhada de uma solicitação de aditivo. Então, vou vou deixar até anotar aqui. Campo para anexar PDF da solicitação de aditivo. Aí, esse essa solicitação de aditivo é esse primeiro documento aqui, viu? Aí, aí esse daqui,

**Deivison Santos:** Certo.

**Fred de Oliveira Roldão:** esse aqui é só um despacho paraa procuradoria. Então, como ele é só um despacho, é melhor a gente gerar ele junto com isso aqui, com esse primeiro aqui,

**Deivison Santos:** Bom.

**Fred de Oliveira Roldão:** pra gente não ter só um campo a mais que nem a gente nem que a gente faz no no nosso processo para resumir.

**Deivison Santos:** Uhum. Sim.

**Fred de Oliveira Roldão:** Então, esse aqui gera junto com esse aqui.

**Deivison Santos:** Sim.

**Fred de Oliveira Roldão:** Aí tem o solicito o parecer jurídico que não precisa de nada, que é só clicar lá, ele gera, já puxa as informações todas já.

### **00:14:05**

**Fred de Oliveira Roldão:** Tá aí, isso aqui é só o tu tem que configurar lá pro sistema marcar automático, entendeu? Por exemplo, se for um aditivo de valor aí,

**Deivison Santos:** Ah,

**Fred de Oliveira Roldão:** aí ele ele marca aqui um xizinho aqui.

**Deivison Santos:** sim,

**Fred de Oliveira Roldão:** Aí tu pode mudar o design lá.

**Deivison Santos:** mas ele no caso ele pode ele pode mostrar todos esses quatro,

**Fred de Oliveira Roldão:** Pode, pode. Ele só deixa a marcação.

**Deivison Santos:** mas ele pronto.

**Fred de Oliveira Roldão:** Vou deixar a marcaçãozinha aí. Beleza. Aí aí o aparecer é outro documento. A gente já tá com dois, né? E aí essa autorização do prefeito aqui é o terceiro,

**Deivison Santos:** Uhum.

**Fred de Oliveira Roldão:** é o é o terceiro. É bom ter separado para não sair junto com procur com o procurador e o

**Deivison Santos:** Uhum.

**Fred de Oliveira Roldão:** aditivo são quatro, entendeu? Aí no aditivo aqui,

**Deivison Santos:** S.

**Fred de Oliveira Roldão:** no aditivo aqui ele vai puxar isso aqui, ele puxa do contrato, é os dados das empresas da prefeitura.

### **00:14:53** {#00:14:53}

**Fred de Oliveira Roldão:** Isso aqui a gente preenche, que é bom ter lá aqui no termo aditivo, é porque isso aqui é outra coisa, porque logo quando a gente criar o processo, o ideal já é a gente dar as informações do aditivo, que desde o começo aqui ele vai pedir, ó, quantos meses são, isso tudo, entendeu? Então,

**Deivison Santos:** Não,

**Fred de Oliveira Roldão:** logo no começo do

**Deivison Santos:** no caso é junto com a solicitação.

**Fred de Oliveira Roldão:** é no não é no é é verdade, mas faz sentido aqui. Aí deixa eu então deixa eu colocar aqui eh no caso de valor e então já já que a gente vai puxar direto para cá, aquela questão da planilha, ela pode vir aqui para dentro, né?

**Deivison Santos:** Eh

**Fred de Oliveira Roldão:** Questão da gente selecionar a porcentagem do valor e tudo, né? Porque já que já que a gente vai abrir uma aba todinha de um processo novo, já puxa ela para cá para não ficar mais prático, né?

**Deivison Santos:** Certo.

**Fred de Oliveira Roldão:** De valor,

**Deivison Santos:** Ja.

**Fred de Oliveira Roldão:** a primeira coisa que tem que fazer já é informar a quantidade do valor, no caso deitivo de valor, ter eh o campo para digitar a porcentagem e aparecer a planilha com as quantidades aditivadas.

### **00:16:15**

**Fred de Oliveira Roldão:** e o valor do aditivo. Aí, sempre lembrando que o aditivo é é em questão da aditiva a quantidade, entendeu? Não o valor em si.

**Deivison Santos:** Sì.

**Fred de Oliveira Roldão:** Aí, no caso de prazo, a, eh, ter o campo para informar os meses, pode ser dias também, entendeu? Mas deixar meses aqui. Aí a gente informou isso aqui. O resto, acredito eu, que o sistema já puxe tudo.

**Deivison Santos:** do sistema,

**Fred de Oliveira Roldão:** É, já puxe tudo.

**Deivison Santos:** né?

**Fred de Oliveira Roldão:** Que isso aqui ele puxou do contrato. Isso aqui a gente vai informar aqui lá no começo, né, que é o os termos do aditivo, número do contrato, prefeitura, objeto, quem assinou é o mesmo que assina o contrato. Pronto. Basicamente é só isso. Não, bicho de sete de cabeças faz chato de entender. Entendeu? Mas não

**Deivison Santos:** Não é ontem eu tinha dado uma entendida,

### **00:17:09** {#00:17:09}

**Fred de Oliveira Roldão:** é

**Deivison Santos:** mas eh como não gravou, Marcha, eu preferi eh te perguntando

**Fred de Oliveira Roldão:** melhor não. Aditivo, eu não mexo muito com aditivo,

**Deivison Santos:** Да.

**Fred de Oliveira Roldão:** então nem eu entendo tanto. Mas basicamente é isso, entendeu? É, é a gente a gente registrar esses incidentes aí. E como a gente vai criar uma página só por processo, aparecia aqui eh aqui na na aditivo, tanto na aba processo, eu não quero que ele apareça. É até uma questão interessante, né? como é que é pra gente encontrar os aditivos? Porque assim, eu não queria que ele aparecesse aqui nessas abas aqui para porque senão ia ficar muito poluído e ele ele o aditivo é vinculado a um processo, entendeu? Kom.

**Deivison Santos:** A a um processo ou a um

**Fred de Oliveira Roldão:** Isso. E e é aos dois,

**Deivison Santos:** contrato.

**Fred de Oliveira Roldão:** porque, por exemplo, o contrato ele vem de um processo,

**Deivison Santos:** Não, mas tipo assim,

### **00:18:06**

**Fred de Oliveira Roldão:** né?

**Deivison Santos:** se eu vincular o ao contrato, ele automaticamente ele tá vinculando ao processo,

**Fred de Oliveira Roldão:** Sim.

**Deivison Santos:** porque o contrato,

**Fred de Oliveira Roldão:** Pois é. Eu queria,

**Deivison Santos:** porque assim,

**Fred de Oliveira Roldão:** eu queria que ele aparecesse também.

**Deivison Santos:** ou eu vinculo com o processo ou eu vinculo com o contrato, sabe?

**Fred de Oliveira Roldão:** Não consegue vincular para aparecer tanto aqui no processo quanto aqui na aba contrato.

**Deivison Santos:** Não dá, dá, não é?

**Fred de Oliveira Roldão:** Não.

**Deivison Santos:** É porque o contrato que aparece aí é o mesmo que aparece lá lá no outro, né? Então

**Fred de Oliveira Roldão:** Pois é. É porque é porque é porque assim o o aditivo ele é o aditivo do contrato,

**Deivison Santos:** é.

**Fred de Oliveira Roldão:** mas por exemplo, a gente gera um processo desse aqui, é o qual o número?

**Deivison Santos:** Uhum.

**Fred de Oliveira Roldão:** Um processo 040\.

**Deivison Santos:** Eu sei.

**Fred de Oliveira Roldão:** O aditivo ele vai ficar vai se botar dentro da pasta do processo 040, entendeu?

**Deivison Santos:** Entendi.

### **00:18:44**

**Fred de Oliveira Roldão:** Aí, aí,

**Deivison Santos:** Então a gente bota aí dentro desses três pontinhos,

**Fred de Oliveira Roldão:** por isso que tá aqui.

**Deivison Santos:** pô. Uma Sim. Aí eu boto lá

**Fred de Oliveira Roldão:** Tá bom. Pode ser também,

**Deivison Santos:** aditivo.

**Fred de Oliveira Roldão:** pode ser porque eu não queria colocar aqui que polui muito, mas o ideal também era a gente ter uma uma na é aí quando a gente quiser pesquisar o aditivo,

**Deivison Santos:** Eh,

**Fred de Oliveira Roldão:** a gente vem aqui e pesquisa o contrato e vai e a gente vai abrir eh outra aba de

**Deivison Santos:** ou cria outra aba, né? Outra aba aí na lateral.

**Fred de Oliveira Roldão:** aditivos, mas fica muita coisa.

**Deivison Santos:** Mas aí fica fica muito eh eu já tô achando muita coisa

**Fred de Oliveira Roldão:** Eh, eu acho que é porque no nosso no nosso controle que a gente do drive hoje,

**Deivison Santos:** aí.

**Fred de Oliveira Roldão:** o aditivo fica dentro da pasta do processo. Então, se a pessoa quiser procurar aditivo, ela tem que procurar o processo. Então, a gente pode fazer do mesmo jeito, seguir a mesma lógica, né? A gente coloca dentro do processo e dentro aqui da aba contrato. A pessoa quer encontrar aditivo,

**Deivison Santos:** M.

**Fred de Oliveira Roldão:** ela pega o nome da empresa, o número do contrato, procura aqui, vai encontrar, né, no nos negócios. A gente pode fazer dessa forma. E aí, eu acho que é basicamente isso. Deixa eu salvar isso aqui para te mandar aí já marcado que eu te

**Deivison Santos:** Vou parar de gravar

**Fred de Oliveira Roldão:** mandei.

**Deivison Santos:** aqui,

**Fred de Oliveira Roldão:** Eu acho que acho que é basicamente isso, questão do aditivo, né? Aí esses outros, esses esses outros aqui são mais complexos,

**Deivison Santos:** certo?

**Fred de Oliveira Roldão:** mas eu vou ter que ver. Mas eu basicamente esses iniciais aí são esses aí. Qualquer dúvida que tu for tendo, tu pode ir me lembrando, viu? Aí,

**Deivison Santos:** Co?

**Fred de Oliveira Roldão:** só lembrar que tu pode me perguntando

### **A transcrição foi encerrada após 00:20:03**

*Esta transcrição editável foi gerada por computador e pode conter erros. As pessoas também podem alterar o texto depois que ele for criado.*