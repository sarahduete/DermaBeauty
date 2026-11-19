
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Derma Beauty | Cuidado e Beleza</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Cormorant+Garamond:wght@400;500;600&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="../css/landing.css">
</head>

<body>

<header class="header">
    <a href="index.php" class="logo">
        <span>Derma</span> Beauty
        <small>ESTÉTICA E CUIDADO</small>
    </a>

    <nav class="nav">
        <a href="#inicio">Início</a>
        <a href="#sobre">Sobre</a>
        <a href="#funcionalidades">Funcionalidades</a>
        <a href="#contato">Contato</a>
    </nav>

    <a href="cliente/login.php" class="botao-header">Acessar minha conta</a>
</header>

<main>

    <!-- INÍCIO -->
    <section class="hero" id="inicio">

        <div class="hero-conteudo">
            <span class="subtitulo">DERMA BEAUTY · ESTÉTICA E BEM-ESTAR</span>

            <h1>
                Sua beleza merece
                <span>cuidado especial.</span>
            </h1>

            <p>
                Conheça a Derma Beauty, uma plataforma desenvolvida
                para facilitar seu acesso aos tratamentos estéticos,
                agendamentos e acompanhamento dos seus horários.
            </p>

            <div class="hero-botoes">
                <a href="#funcionalidades" class="botao-principal">
                    Conhecer a plataforma
                </a>

                <a href="cliente/cadastro.php" class="botao-secundario">
                    Criar minha conta
                </a>
            </div>

            <div class="hero-info">
                <span>✦ Atendimento organizado</span>
                <span>✦ Agendamento online</span>
            </div>
        </div>

        <div class="hero-imagem">
            <img
                src="https://images.unsplash.com/photo-1570172619644-dfd03ed5d881?auto=format&fit=crop&w=1000&q=85"
                alt="Ambiente de tratamento estético"
            >

            <div class="imagem-legenda">
                <span>DERMA BEAUTY</span>
                <p>Seu momento de cuidado começa aqui.</p>
            </div>
        </div>

    </section>

    <!-- SOBRE -->
    <section class="sobre" id="sobre">

        <div class="sobre-imagem">
            <img
                src="https://images.unsplash.com/photo-1616394584738-fc6e612e71b9?auto=format&fit=crop&w=900&q=85"
                alt="Cuidados com a pele"
            >
        </div>

        <div class="sobre-conteudo">
            <span class="subtitulo">CONHEÇA A DERMA BEAUTY</span>

            <h2>
                Um espaço pensado para
                <span>facilitar seu cuidado.</span>
            </h2>

            <p>
                A Derma Beauty é uma plataforma voltada para a
                organização de serviços estéticos e para a experiência
                dos clientes.
            </p>

            <p>
                Por meio do sistema, é possível conhecer os tratamentos,
                consultar informações, realizar agendamentos e acompanhar
                seus compromissos de maneira simples e prática.
            </p>

            <a href="usuario/tratamentos.php" class="link-seta">
                Conhecer tratamentos →
            </a>
        </div>

    </section>

    <!-- FUNCIONALIDADES -->
    <section class="funcionalidades" id="funcionalidades">

        <div class="secao-titulo">
            <span class="subtitulo">EXPLORE A PLATAFORMA</span>

            <h2>
                Tudo o que você precisa,
                <span>em um só lugar.</span>
            </h2>

            <p>
                Descubra os recursos disponíveis na Derma Beauty.
            </p>
        </div>

        <div class="cards">

            <article class="card">
                <div class="card-icone">✧</div>
                <h3>Tratamentos</h3>
                <p>
                    Consulte os tratamentos disponíveis, conheça
                    suas descrições e confira os valores.
                </p>
                <a href="usuario/tratamentos.php">Explorar tratamentos →</a>
            </article>

            <article class="card">
                <div class="card-icone">♡</div>
                <h3>Agendamento online</h3>
                <p>
                    Escolha o procedimento, consulte as datas
                    e selecione um horário disponível.
                </p>
                <a href="cliente/login.php">Agendar horário →</a>
            </article>

            <article class="card">
                <div class="card-icone">○</div>
                <h3>Área do cliente</h3>
                <p>
                    Tenha acesso à sua conta e acompanhe
                    seus agendamentos em um só lugar.
                </p>
                <a href="cliente/cadastro.php">Criar minha conta →</a>
            </article>

            <article class="card">
                <div class="card-icone">⌘</div>
                <h3>Painel administrativo</h3>
                <p>
                    Ambiente destinado à administração
                    dos tratamentos e agendamentos da clínica.
                </p>
                <a href="admin/index.php">Acessar administração →</a>
            </article>

        </div>

    </section>

    <!-- COMO FUNCIONA -->
    <section class="como-funciona">

        <div class="secao-titulo">
            <span class="subtitulo">SIMPLES E PRÁTICO</span>
            <h2>Como começar?</h2>
        </div>

        <div class="passos">

            <div class="passo">
                <span>01</span>
                <h3>Crie sua conta</h3>
                <p>Cadastre seus dados para acessar a área do cliente.</p>
            </div>

            <div class="passo">
                <span>02</span>
                <h3>Escolha seu tratamento</h3>
                <p>Conheça as opções disponíveis na plataforma.</p>
            </div>

            <div class="passo">
                <span>03</span>
                <h3>Agende seu horário</h3>
                <p>Selecione uma data e um horário disponível.</p>
            </div>

            <div class="passo">
                <span>04</span>
                <h3>Acompanhe seu agendamento</h3>
                <p>Consulte as informações na sua área do cliente.</p>
            </div>

        </div>

    </section>

    <!-- CHAMADA FINAL -->
    <section class="chamada" id="contato">

        <span class="subtitulo">SEU CUIDADO COMEÇA AQUI</span>

        <h2>
            Conheça uma nova forma
            <span>de organizar seus cuidados.</span>
        </h2>

        <p>
            Acesse a Derma Beauty e descubra todos os recursos
            disponíveis para você.
        </p>

        <a href="cliente/cadastro.php" class="botao-principal">
            Começar agora
        </a>

    </section>

</main>

<footer class="footer">

    <a href="index.php" class="logo">
        <span>Derma</span> Beauty
        <small>ESTÉTICA E CUIDADO</small>
    </a>

    <p>© 2026 Derma Beauty. Todos os direitos reservados.</p>

    <a href="#inicio">Voltar ao início ↑</a>

</footer>

</body>
</html>
