<?php
    session_start();
    require_once 'conexao.php';

    // VERIFICA SE O USUÁRIO TEM PERMISSÃO
    // SUPONDO QUE O PERFIL 1 SEJA O ADMINISTRADOR
    if ($_SESSION['perfil'] != 1 && $_SESSION['perfil'] != 3) {
        echo "Acesso negado!";
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $nome_fornecedor = $_POST['nome_fornecedor'];
        $endereco = $_POST['endereco'];
        $telefone = $_POST['telefone'];
        $email = $_POST['email'];
        $contato = $_POST['contato'];

        $sql = "INSERT INTO fornecedor (nome_fornecedor, endereco, telefone, email, contato) VALUES (:nome_fornecedor, :endereco, :telefone, :email, :contato)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':nome_fornecedor', $nome_fornecedor);
        $stmt->bindParam(':endereco', $endereco);
        $stmt->bindParam(':telefone', $telefone);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':contato', $contato);

        if ($stmt->execute()) {
            echo "<script>alert('Fornecedor cadastrado com sucesso!');</script>";
        } else {
            echo "<script>alert('Erro ao cadastrar fornecedor!');</script>";
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
    <title> Cadastrar Fornecedor </title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .telefone {
                width: 80%; /* Ocupa toda a largura do formulário */
                padding: 8px;
                margin-bottom: 10px;
                border: 1px solid #ccc;
                border-radius: 5px;
                font-size: 16px;
            }

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
                margin-top: 70px;
            }
    </style>
</head>
<body>
    <h2> Cadastrar Fornecedor </h2>

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

    <form action="cadastro_fornecedor.php" method="POST" id="formCadastro">
        <label for="nome_fornecedor"> Nome do Fornecedor: </label>
        <input type="text" name="nome_fornecedor" id="nome_fornecedor" required>

        <label for="endereco"> Endereço: </label>
        <input type="text" name="endereco" id="endereco" required>

        <label for="telefone"> Telefone: </label>
        <input type="tel" name="telefone" id="telefone" class="telefone" required>

        <label for="email"> E-mail: </label>
        <input type="email" name="email" id="email" required>

        <label for="contato"> Contato: </label>
        <input type="text" name="contato" id="contato" required>

        <button type="submit"> Salvar </button>
        <button type="reset"> Cancelar </button>
    </form>
    
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
        document.getElementById("formCadastro").addEventListener("submit", function(event) {
            let nome = document.getElementById("nome_fornecedor").value.trim();
            let contato = document.getElementById("contato").value.trim();

            // Regex: aceita apenas letras (maiúsculas e minúsculas) e espaços
            let nomeRegex = /^[A-Za-zÀ-ÿ\s]+$/;
            let contatoRegex = /^[A-Za-zÀ-ÿ\s]+$/;

            if (nome.length < 3) {
                alert("O nome deve conter pelo menos 3 caracteres.")
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