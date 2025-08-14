<?php
    session_start();
    require_once "conexao.php";

    // VERIFICA SE O USUÁRIO TEM PERMISSÃO DE ADM
    if ($_SESSION['perfil'] !=1) {
        echo "<script>alert('Acesso Negado!'); window.location.href='index.php';</script>";
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
                exit();
            }
        }
    }
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
</head>
<body>
    <h2> Alterar Usuário </h2>

    <form action="alterar_usuario.php" method="POST">
    <label for="busca"> Digite o ID ou NOME do Usuário: </label>
        <input type="text" name="busca_usuario" id="busca_usuario" required onkeyup="buscarSugestões()">

        <!-- DIV PARA EXCLUIR SUGESTÕES DE USUÁRIOS -->
         <div id="sugestoes"></div>
        <button type="submit"> Buscar </button>
    </form>

    <?php if ($usuario) { ?>

        <!-- FORMULÁRIO PARA ALTERAR USUÁRIO -->
         <form action="processa_alteracao_usuario.php" method="POST">
            <input type="hidden" name="id_usuario" value="<?= htmlspecialchars($usuario['id_usuario']) ?>">

            <label for="nome"> Nome: </label>
            <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($usuario['nome']) ?>" required>

            <label for="email"> E-mail: </label>
            <input type="email" id="email"name="email" value="<?= htmlspecialchars($usuario['email']) ?>" required>

            <label for="id_perfil"> Perfil: </label>
            <select id="id_perfil" name="id_perfil"> 
                <option value="1"><?= $usuario['id_perfil'] == 1 ? 'select' : '' ?> Administrador </option>
                <option value="1"><?= $usuario['id_perfil'] == 2 ? 'select' : '' ?> Secretária </option>
                <option value="1"><?= $usuario['id_perfil'] == 3 ? 'select' : '' ?> Almoxarife </option>
                <option value="1"><?= $usuario['id_perfil'] == 4 ? 'select' : '' ?> Cliente </option>
            </select>
        </form>

    <?php } ?>
</body>
</html>