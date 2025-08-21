<?php
    session_start();
    require_once "conexao.php";

    // VERIFICA SE O USUÁRIO TEM PERMISSÃO DE ADM
    if ($_SESSION['perfil'] !=1 && $_SESSION['perfil'] !=2 && $_SESSION['perfil'] !=3) {
        echo "<script>alert('Acesso Negado!');window.location.href='principal.php';</script>";
        exit();
    }

    // INICIALIZA VARIÁVEIS
    $fornecedor = null;

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (!empty($_POST['busca_fornecedor'])) {
            $busca = trim($_POST['busca_fornecedor']);

            // VERIFICA SE A BUSCA É UM NÚMERO (id) OU UM NOME
            if (is_numeric($busca)) {
                $sql = "SELECT * FROM fornecedor WHERE id_fornecedor = :busca";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(":busca", $busca, PDO::PARAM_INT);
            } else {
                $sql = "SELECT * FROM fornecedor WHERE nome_fornecedor LIKE :busca_nome";
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':busca_nome', "$busca%", PDO::PARAM_STR);
            }
            
            $stmt->execute();
            $fornecedor = $stmt->fetch(PDO::FETCH_ASSOC);

            // SE O FORNECEDOR NAO FOR ENCONTRADO, EXIBE UM ALERTA
            if (!$fornecedor) {
                echo "<script>alert('Fornecedor não encontrado!');</script>";
            }
        }
    }

    // OBTENDO O NOME DO PERFIL DO USUÁRIO LOGADO
    $id_perfil = $_SESSION['perfil'];
    $sqlPerfil = "SELECT nome_perfil FROM perfil WHERE id_perfil = :id_perfil";
    $stmtPerfil = $pdo->prepare($sqlPerfil);
    $stmtPerfil->bindParam(':id_perfil', $id_perfil);
    $stmtPerfil->execute();
    $perfil = $stmtPerfil->fetch(PDO::FETCH_ASSOC);
    $nome_perfil = $perfil['nome_perfil'];

    // DEFINIÇÃO DAS PERMISSÕES POR PERFIL
    $permissoes = [
        // PERMISSÕES DO ADMIN
        1 => ["Cadastrar"=>["cadastro_usuario.php", "cadastro_perfil.php", "cadastro_cliente.php", "cadastro_fornecedor.php", "cadastro_produto.php", "cadastro_funcionario.php"],
              "Buscar"=>["buscar_usuario.php", "buscar_perfil.php", "buscar_cliente.php", "buscar_fornecedor.php", "buscar_produto.php", "buscar_funcionario.php"],
              "Alterar"=>["alterar_usuario.php", "alterar_perfil.php", "alterar_cliente.php", "alterar_fornecedor.php", "alterar_produto.php", "alterar_funcionario.php"],
              "Excluir"=>["excluir_usuario.php", "excluir_perfil.php", "excluir_cliente.php", "excluir_fornecedor.php", "excluir_produto.php", "excluir_funcionario.php"]],

        // PERMISSÕES DA SECRETÁRIA
        2 => ["Cadastrar"=>["cadastro_cliente.php"],
              "Buscar"=>["buscar_cliente.php", "buscar_fornecedor.php", "buscar_produto.php"],
              "Alterar"=>["alterar_fornecedor.php", "alterar_produto.php"],
              "Excluir"=>["excluir_produto.php"]],

        // PERMISSÕES DO ALMOXARIFE
        3 => ["Cadastrar"=>["cadastro_fornecedor.php", "cadastro_produto.php"],
              "Buscar"=>["buscar_cliente.php", "buscar_fornecedor.php", "buscar_produto.php"],
              "Alterar"=>["alterar_fornecedor.php", "alterar_produto.php"],
              "Excluir"=>["excluir_produto.php"]],

        // PERMISSÕES DO CLIENTE
        4 => ["Cadastrar"=>["cadastro_cliente.php"],
              "Buscar"=>["buscar_cliente.php"],
              "Alterar"=>["alterar_cliente.php"]],
    ];

    // OBTENDO AS OPÇÕES DISPONIVEIS PARA O PERFIL DO USUÁRIO LOGADO
    $opcoes_menu = $permissoes["$id_perfil"];
?>


<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Alterar Fornecedor </title>
    <link rel="stylesheet" href="styles.css">

    <!-- CERTIFIQUE-SE DE QUE O JAVASCRIPT ESTÁ SENDO CARREGADO CORRETAMENTE -->
    <script src="scripts.js"></script>

    <style>
        .voltar {
                width: 70%;
                padding: 10px 100px;
                background-color: #007bff; /* Azul bonito */
                color: white;
                border: none;
                border-radius: 5px;
                font-size: 16px;
                cursor: pointer;
                transition: 0.3s;
                text-decoration: none;
            }   

            .voltar:hover {
                background-color: #0056b3; /* Azul mais escuro ao passar o mouse */
            }

            footer {
                background-color: #333;
                color: white;
                padding: 15px;
                margin-top: 180px;
            }
    </style>
</head>
<body>
    <h2> Alterar Fornecedor </h2>

    <nav>
        <ul class="menu">
            <?php foreach($opcoes_menu as $categoria => $arquivos) { ?>
                <li class="dropdown">
                    <a href="#"><?= $categoria ?></a>

                    <ul class="dropdown-menu">
                        <?php foreach($arquivos as $arquivo) { ?>
                            <li>   
                                <a href="<?= $arquivo ?>"><?= ucfirst(str_replace("_", " ", basename($arquivo, ".php"))) ?></a>
                            </li>
                        <?php } ?>
                    </ul>
                </li>
            <?php } ?>
        </ul>
    </nav>

    <form action="alterar_fornecedor.php" method="POST">
    <label for="busca"> Digite o ID ou NOME do Fornecedor: </label>
        <input type="text" name="busca_fornecedor" id="busca_fornecedor" required onkeyup="buscarSugestões()">

        <!-- DIV PARA EXCLUIR SUGESTÕES DE FORNECEDORES -->
         <div id="sugestoes"></div>
        <button type="submit"> Buscar </button>
    </form>

    <?php if ($fornecedor) { ?>

        <!-- FORMULÁRIO PARA ALTERAR USUÁRIO -->
         <form id="formProcessar" action="processa_alteracao_fornecedor.php" method="POST">
            <input type="hidden" name="id_fornecedor" value="<?= htmlspecialchars($fornecedor['id_fornecedor']) ?>">

            <label for="nome"> Nome do Fornecedor: </label>
            <input type="text" id="nome_fornecedor" name="nome_fornecedor" value="<?= htmlspecialchars($fornecedor['nome_fornecedor']) ?>" required>

            <label for="nome"> Endereço: </label>
            <input type="text" id="endereco" name="endereco" value="<?= htmlspecialchars($fornecedor['endereco']) ?>" required>
            
            <label for="nome"> Telefone: </label>
            <input type="text" id="telefone" name="telefone" value="<?= htmlspecialchars($fornecedor['telefone']) ?>" required>

            <label for="email"> E-mail: </label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($fornecedor['email']) ?>" required>

            <label for="nome"> Contato: </label>
            <input type="text" id="contato" name="contato" value="<?= htmlspecialchars($fornecedor['contato']) ?>" required>

            <button type="submit"> Alterar </button>
            <button type="reset"> Cancelar </button>
        </form>

    <?php } ?>

    <a class="voltar" href="principal.php"> Voltar </a>

    <script>
        const telefone = document.getElementById("telefone");
        telefone.addEventListener('input', function () {
            let telefone = this.value.replace(/\D/g, "");

            if (telefone.length > 10) {
                    telefone = telefone.replace(/^(\d{2})(\d{5})(\d{4}).*/, "($1) $2-$3");
                } else if (telefone.length > 5) {
                    telefone = telefone.replace(/^(\d{2})(\d{4})(\d{0,4}).*/, "($1) $2-$3");
                } else if (telefone.length > 2) {
                    telefone = telefone.replace(/^(\d{2})(\d{0,5}).*/, "($1) $2");
                } else {
                    telefone = telefone.replace(/^(\d*)/, "($1");
                }

            this.value = telefone;
        });
    </script>

    <script>
        document.getElementById("formProcessar").addEventListener("submit", function(event) {
            let nome = document.getElementById("nome_fornecedor").value.trim();
            let contato = document.getElementById("contato").value.trim();

            // Regex: aceita apenas letras (maiúsculas e minúsculas) e espaços
            let nomeRegex = /^[A-Za-zÀ-ÿ\s]+$/;
            let contatoRegex = /^[A-Za-zÀ-ÿ\s]+$/;

            if (nome.length < 3) {
                alert("O nome deve conter pelo menos 3 caracteres.");
                event.preventDefault();
                return;
            }

            if (!nomeRegex.test(nome)) {
                alert("O nome não pode conter números ou caracteres especiais!");
                event.preventDefault();
                return;
            }

            if (contato.length < 3) {
                alert("O contato deve conter pelo menos 3 caracteres.");
                event.preventDefault();
                return;
            }

            if (!contatoRegex.test(contato)) {
                alert("O contato não pode conter números ou caracteres especiais!");
                event.preventDefault();
                return;
            }
        });
    </script>
</body>
<footer>
    Lara Gorito Barbosa de Souza
</footer>
</html>