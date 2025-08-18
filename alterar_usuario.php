<?php
    session_start();
    require_once "conexao.php";

    // VERIFICA SE O USUÁRIO TEM PERMISSÃO DE ADM
    if ($_SESSION['perfil'] !=1) {
        echo "<script>alert('Acesso Negado!');window.location.href='principal.php';</script>";
        exit();
    }

    // INICIALIZA VARIÁVEIS
    $usuario = null;

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (!empty($_POST['busca_usuario'])) {
            $busca = trim($_POST['busca_usuario']);

            // VERIFICA SE A BUSCA É UM NÚMERO (id) OU UM NOME
            if (is_numeric($busca)) {
                $sql = "SELECT * FROM usuario WHERE id_usuario = :busca";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(":busca", $busca, PDO::PARAM_INT);
            } else {
                $sql = "SELECT * FROM usuario WHERE nome LIKE :busca_nome";
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':busca_nome', "$busca%", PDO::PARAM_STR);
            }
            
            $stmt->execute();
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            // SE O USUÁRIO NAO FOR ENCONTRADO, EXIBE UM ALERTA
            if (!$usuario) {
                echo "<script>alert('Usuário não encontrado!');</script>";
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
    <title> Alterar Usuário </title>
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
    <h2> Alterar Usuário </h2>

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

    <form action="alterar_usuario.php" method="POST">
    <label for="busca"> Digite o ID ou NOME do Usuário: </label>
        <input type="text" name="busca_usuario" id="busca_usuario" required onkeyup="buscarSugestões()">

        <!-- DIV PARA EXCLUIR SUGESTÕES DE USUÁRIOS -->
         <div id="sugestoes"></div>
        <button type="submit"> Buscar </button>
    </form>

    <?php if ($usuario) { ?>

        <!-- FORMULÁRIO PARA ALTERAR USUÁRIO -->
         <form id="formProcessar" action="processa_alteracao_usuario.php" method="POST">
            <input type="hidden" name="id_usuario" value="<?= htmlspecialchars($usuario['id_usuario']) ?>">

            <label for="nome"> Nome: </label>
            <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($usuario['nome']) ?>" required>

            <label for="email"> E-mail: </label>
            <input type="email" id="email"name="email" value="<?= htmlspecialchars($usuario['email']) ?>" required>

            <label for="id_perfil"> Perfil: </label>
            <select id="id_perfil" name="id_perfil"> 
                <option value="1"<?= $usuario['id_perfil'] == 1 ? 'select' : '' ?>> Administrador </option>
                <option value="2"<?= $usuario['id_perfil'] == 2 ? 'select' : '' ?>> Secretária </option>
                <option value="3"<?= $usuario['id_perfil'] == 3 ? 'select' : '' ?>> Almoxarife </option>
                <option value="4"<?= $usuario['id_perfil'] == 4 ? 'select' : '' ?>> Cliente </option>
            </select>

            <!-- SE O USUÁRIO LOGADO FOR ADM, EXIBIR OPÇÃO DE ALTERAR SENHA -->
            <?php if ($_SESSION['perfil'] == 1) { ?>
                <label for="nova_senha"> Nova Senha: </label>
                <input type="password" id="nova_senha" name="nova_senha">
            <?php } ?>

            <button type="submit"> Alterar </button>
            <button type="reset"> Cancelar </button>
        </form>

    <?php } ?>

    <a class="voltar" href="principal.php"> Voltar </a>

    <script>
        document.getElementById("formProcessar").addEventListener("submit", function(event) {
            let nome = document.getElementById("nome").value.trim();

            // Regex: aceita apenas letras (maiúsculas e minúsculas) e espaços
            let nomeRegex = /^[A-Za-zÀ-ÿ\s]+$/;

            if (!nomeRegex.test(nome)) {
                alert("O nome não pode conter números ou caracteres especiais!");
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