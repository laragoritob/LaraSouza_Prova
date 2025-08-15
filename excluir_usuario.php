<?php
    session_start();
    require_once 'conexao.php';

    // VERIFICA SE O USUÁRIO TEM PERMISSÃO DE ADM
    if ($_SESSION['perfil'] !=1) {
        echo "<script>alert('Acesso Negado!'); window.location.href='principal.php';</script>";
        exit();
    }

    // INICIALIZA VARIÁVEIS
    $usuario = [];

    // BUSCA TODODS OS USUÁRIOS CADASTRADOS EM ORDEM ALFABÉTICA
    $sql = "SELECT * FROM usuario ORDER BY nome ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // SE UM ID FOR PASSADO VIA GET, EXCLUIR O USUÁRIO
    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        $id_usuario = $_GET['id'];

        // EXCLUI O USUÁRIO DO BANCO DE DADOS
        $sql = "DELETE FROM usuario WHERE id_usuario = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id_usuario);
        
        if ($stmt->execute()) {
            echo "<script>alert('Usuário excluído com sucesso!'); window.location.href='excluir_usuario.php';</script>";
        } else {
            echo "<script>alert('Erro ao excluir o usuário.');</script>";
        }
    }
?>


<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Excluir Usuário </title>
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
            width: 70%;
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
</style>
</head>
<body>
    <h2> Excluir Usuário </h2>

    <?php if (!empty($usuarios)) { ?>

        <table class="table table-success table-striped-columns"> 
            <tr>
                <th> ID: </th>
                <th> Nome: </th>
                <th> E-mail: </th>
                <th> Perfil: </th>
                <th> Ações: </th>
            </tr>

            <?php foreach ($usuarios as $usuario) { ?>

            <tr>
                <td><?= htmlspecialchars($usuario['id_usuario']) ?></td>
                <td><?= htmlspecialchars($usuario['nome']) ?></td>
                <td><?= htmlspecialchars($usuario['email']) ?></td>
                <td><?= htmlspecialchars($usuario['id_perfil']) ?></td>
                <td>
                    <a href="excluir_usuario.php?id=<?= htmlspecialchars($usuario['id_usuario']) ?>" onclick="return confirm('Tem certeza que deseja excluir este usuário?')" > Excluir </a>
                </td>
            </tr>

            <?php } ?>
        </table>

    <?php } else { ?>
        <p> Nenhum usuário encontrado. </p>
    <?php } ?>

    <br>
    <a class="voltar" href="principal.php"> Voltar </a>
</body>
</html>