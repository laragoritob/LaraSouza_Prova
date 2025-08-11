<?php
    session_start();
    require_once "conexao.php";

    // VERIFICA SE O USUÁRIO TEM PERMISSÃO DE ADM OU SECRETÁRIA
    if ($_SESSION['perfil'] !=1 && $_SESSION['perfil'] !=2) {
        echo "<script>alert('Acesso Negado!'); window.location.href='index.php';</script>";
        exit();
    }

    $usuario = [];  // INICIALIZA A VARÁVEL PARA EVITAR ERROS

    // SE O FORMULÁRIO FOR ENVIADO, BUSCA O USUÁRIO PELO ID OU NOME
    if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['busca'])) {
        $busca = trim($_POST['busca']);
        
        // VERIFICA SE A BUSCA É UM NÚMERO OU UM NOME
        if (is_numeric($busca)) {
            $sql = "SELECT * FROM usuario WHERE id_usuario = :busca ORDER BY nome ASC";

            $stmt = $pdo->prepare($sql);
            $stmt->bind_param(":busca", $busca, PDO::PARAM_INT);
        } else {
            $sql = "SELECT * FROM usuarios WHERE nome LIKE :busca_nome ORDER BY nome ASC";

            $stmt = $pdo->prepare($sql);
            $stmt->bind_param(":busca_nome", "%$busca%", PDO::PARAM_STR);
        }
    } else {
        $sql = "SELECT * FROM usuario ORDER BY nome ASC";
        $stmt = $pdo->prepare($sql);
    }

    $stmt->execute();
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>