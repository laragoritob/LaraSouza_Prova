<?php
    session_start();
    require_once "conexao.php";

    // VERIFICA SE O USUÁRIO TEM PERMISSÃO DE ADM OU SECRETÁRIA
    if ($_SESSION['perfil'] !=1 && $_SESSION['perfil'] !=2 && $_SESSION['perfil'] !=3) {
        echo "<script>alert('Acesso Negado!'); window.location.href='principal.php';</script>";
        exit();
    }

    $fornecedores = [];  // INICIALIZA A VARÁVEL PARA EVITAR ERROS

    // SE O FORMULÁRIO FOR ENVIADO, BUSCA O FORNECEDOR PELO ID OU NOME
    if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['busca'])) {
        $busca = trim($_POST['busca']);
        
        // VERIFICA SE A BUSCA É UM NÚMERO OU UM NOME
        if (is_numeric($busca)) {
            $sql = "SELECT * FROM fornecedor WHERE id_fornecedor = :busca ORDER BY nome_fornecedor ASC";

            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(":busca", $busca, PDO::PARAM_INT);
        } else {
            $sql = "SELECT * FROM fornecedor WHERE nome_fornecedor LIKE :busca_nome ORDER BY nome_fornecedor ASC";

            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':busca_nome', "$busca%", PDO::PARAM_STR);
        }
    } else {
        $sql = "SELECT * FROM fornecedor ORDER BY nome_fornecedor ASC";
        $stmt = $pdo->prepare($sql);
    }

    $stmt->execute();
    $fornecedores = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    <title> Buscar Fornecedor </title>
    <link rel="stylesheet" href="styles.css">
    <style>
        tr:nth-child(even) td {
            background-color:rgb(255, 255, 255); 
        }

        th, td {
            padding: 12px;
        }

        th {
            background-color:rgb(0, 0, 0); 
            color: white;
        }

        td {  
            background-color:rgb(221, 221, 221);
        }

        table {
            width: 90%;
            margin: 20px auto;
            border-collapse: collapse;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            font-size: 18px;
            text-align: center;
        }

        .telefone {
                width: 80%; /* Ocupa toda a largura do formulário */
                padding: 8px;
                margin-bottom: 10px;
                border: 1px solid #ccc;
                border-radius: 5px;
                font-size: 16px;
            }

        .excluir, .alterar {
            text-decoration: none;
            font-weight: bold;
        }

        .excluir {
            color: rgb(204, 6, 6);
        }

        .voltar {
            width: 80%;
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
    <h2> Lista de Fornecedores </h2>

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

    <form action="buscar_fornecedor.php" method="POST">
        <label for="busca"> Digite o ID ou NOME do Fornecedor: </label>
        <input type="text" name="busca" id="busca" required>
        <button type="submit"> Pesquisar </button>
    </form>

    <?php if (!empty($fornecedores)) { ?>
        <table>
            <tr>
                <th> ID </th>
                <th> Nome do Fornecedor </th>
                <th> Endereço </th>
                <th> Telefone </th>
                <th> E-mail </th>
                <th> Contato </th>
                <th> Ações </th>
            </tr>

            <?php foreach ($fornecedores as $fornecedor) { ?>
            <tr>
                <td> <?= htmlspecialchars($fornecedor['id_fornecedor']) ?> </td>
                <td> <?= htmlspecialchars($fornecedor['nome_fornecedor']) ?> </td>
                <td> <?= htmlspecialchars($fornecedor['endereco']) ?> </td>
                <td> <?= htmlspecialchars($fornecedor['telefone']) ?> </td>
                <td> <?= htmlspecialchars($fornecedor['email']) ?> </td>
                <td> <?= htmlspecialchars($fornecedor['contato']) ?> </td>
                <td> 
                    <a href="alterar_fornecedor.php?id=<?= htmlspecialchars($fornecedor['id_fornecedor']) ?>" class="alterar"> Alterar </a>
                    |
                    <a href="excluir_fornecedor.php?id=<?= htmlspecialchars($fornecedor['id_fornecedor']) ?>" class="excluir"onclick="return confirm('Tem certeza que deseja excluir este fornecedor?')"> Excluir </a>
                </td>
            </tr>
            <?php } ?>
        </table>

    <?php } else { ?>
        <p> Nenhum fornecedor encontrado. </p>
    <?php } ?>

    <br>
    <a class="voltar" href="principal.php"> Voltar </a>
</body>
<footer>
    Lara Gorito Barbosa de Souza
</footer>
</html>