<?php
    session_start();
    require_once 'conexao.php';

    // VERIFICA SE O USUÁRIO TEM PERMISSÃO DE ADM
    if ($_SESSION['perfil'] !=1) {
        echo "<script>alert('Acesso Negado!'); window.location.href='principal.php';</script>";
        exit();
    }

    // INICIALIZA VARIÁVEIS
    $fornecedor = [];

    // BUSCA TODODS OS USUÁRIOS CADASTRADOS EM ORDEM ALFABÉTICA
    $sql = "SELECT * FROM fornecedor ORDER BY nome_fornecedor ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $fornecedores = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // SE UM ID FOR PASSADO VIA GET, EXCLUIR O USUÁRIO
    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        $id_fornecedor = $_GET['id'];

        // EXCLUI O USUÁRIO DO BANCO DE DADOS
        $sql = "DELETE FROM fornecedor WHERE id_fornecedor = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id_fornecedor);
        
        if ($stmt->execute()) {
            echo "<script>alert('Fornecedor excluído com sucesso!'); window.location.href='excluir_fornecedor.php';</script>";
        } else {
            echo "<script>alert('Erro ao excluir o fornecedor.');</script>";
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
    <title> Excluir Fornecedor </title>
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
            width: 80%;
            margin: 20px auto;
            border-collapse: collapse;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            font-size: 18px;
            text-align: center;
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
                margin-top: 100px;
            }

        .excluir {
            text-decoration: none;
            font-weight: bold;
            color: rgb(204, 6, 6);
        }
</style>
</head>
<body>
    <h2> Excluir Fornecedor </h2>

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