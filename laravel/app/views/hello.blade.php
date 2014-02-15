<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>API Testing Facility</title>
    <script src="refer.js" type="application/javascript"></script>
    <style>
        @import url(//fonts.googleapis.com/css?family=Lato:700);

        body {
            margin: 0;
            font-family: 'Lato', sans-serif;
            color: #999;
        }

        .welcome {
            text-align: center;
        }

        .result {
            color: #222;
            background: #bbb;
            position: relative;
        }

        .photo {
            position: absolute;
            top: 5px;
            right: 5px;
        }


        a, a:visited {
            text-decoration: none;
        }

        h1 {
            font-size: 32px;
            margin: 16px 0 0 0;
        }

        input[type="text"] {
            display: inline;
            color: #999;
            border: 1px solid #999;
            background: transparent;
        }

        button {
            color: #999;
            border: 1px solid #999;
            background: transparent;
            margin-left: 5px;
        }
    </style>
</head>
<body>
<div class="welcome">
    <h1>Enter Postcode: </h1>
    <input type="text" id="postcode">
    <button>Go</button>
</div>
<div class="result"><div class="photo"></div><pre id="result"></pre></div>


<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.0.3/jquery.min.js" type="application/javascript"></script>
<script type="application/javascript">
    $(function () {
        $("button").click(function () {
            $("#result").text("Loading...");
            $.get("/api/email/" + $("#postcode").val() +"?_token={{ Session::token() }}", function (data) {
                $("#result").html(JSON.stringify(data, null, 4));
                $(".photo").html(ReferrerKiller.imageHtml(data.photoUrl));
            });
        });
    });
</script>
</body>
</html>
