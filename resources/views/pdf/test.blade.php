<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Documento</title>
    <style>
        h1{
            text-align: center;
            text-transform: uppercase;
        }
        .contenido{
            font-size: 20px;
        }
        #parrafos{
            background-color: #ffffff;
            margin-botton:15px;
            margin-top:15px;

        }
        #parrafosCapital{
            background-color: #ffffff;
            font-size: 30px;
            text-align: center;
        }
        #logos{
            width:100%;
            background-color: #ccc;
            height: 50px;
            text-align: center;
            margin-left: auto;
            margin-right: auto;
            display: block;
        }
        #segundo{
            color:#44a359;
        }
        #datos{
            text-decoration:line-through;
            border-bottom:1px solid #ccc!important;
            border-color:#ccc;
            width: 100%;
        }
    </style>
</head>
<body>
<hr>
<br>
<div class="contenido">

    <p id="parrafos">
        BIENVENIDO  <b>{{ $nombre }}</b>.
    </p>

</div>
</body>
</html>
