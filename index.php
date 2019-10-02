<?php
    $pokemon = getPokemon(getSearchRequest());

    function getSearchRequest() : string {
        //$searchRequest = "789";
        if (isset($_GET["search_btn"])) { //check if form was submitted
            if ($_GET["search_txt"] == null)
                $searchRequest = "1";
            else
                $searchRequest = $_GET["search_txt"]; //get input text
        } else
            $searchRequest = "1";
        return strtolower($searchRequest);
    }

    //var_dump($pokemon["moves"][0]["move"]);
    //echo $pokemon["moves"][0]["move"]["name"];

    function getPokemon(string $searchRequest) : array {
        $apiUrl = "https://pokeapi.co/api/v2/pokemon/$searchRequest";
        $json = file_get_contents($apiUrl);
        $pokemonInfo = json_decode($json, true);
        $pokemon = ["id", "name", "weight", "moves", "species", "sprite"];
        $pokemon["id"] = $pokemonInfo["id"];
        $pokemon["name"] = $pokemonInfo["name"];
        $pokemon["weight"] = $pokemonInfo["weight"];
        $pokemon["moves"] = getMoves($pokemonInfo["moves"]);
        $pokemon["sprite"] = $pokemonInfo["sprites"]["front_default"];
        $pokemon["species"] = getEvolutions($pokemonInfo["species"]["url"]);
        return $pokemon;
    }

    function getEvolutions(string $url) : array {
        $json = file_get_contents($url);
        $info = json_decode($json, true);
        //var_dump($info["evolution_chain"]["url"]);
        $json = file_get_contents($info["evolution_chain"]["url"]);
        $info = json_decode($json, true);
        $chain = [];
        $chain[] = $info["chain"]["species"]["url"];

        foreach ($info["chain"]["evolves_to"] as $secondEvoSpecies){
            //var_dump($species["species"]["name"]);
            $chain[] = $secondEvoSpecies["species"]["url"];
            foreach ($secondEvoSpecies["evolves_to"] as $finalEvoSpecies){
                $chain[] = $finalEvoSpecies["species"]["url"];
            }
        }
        //return $info["chain"];
        //return $info["chain"]["evolves_to"][0]["evolves_to"][0]["species"]["name"];
        return  $chain; //return evolution chain, first element will always be the originator of the chain
    }

    function getMoves(array $moves) : array {
        $tempMoves = [];
        if (count($moves) <= 4 ){
            foreach ($moves as $move) {
                $tempMoves[] = $move["move"]["name"];
            }
            return $tempMoves;
        }
        else {
            for ($i = 0; $i < 4; $i++){
                $randomIndex = random_int(0, count($moves));
                $tempMoves[] = $moves[$randomIndex]["move"]["name"];
                unset($moves[$randomIndex]);
            }
            return $tempMoves;
        }
    }
    //var_dump(getPokemon($searchRequest));

    ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Pokédex</title>
    <link href="https://fonts.googleapis.com/css?family=Exo+2&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="./assets/css/bootstrap.css" type="text/css">
    <link rel="stylesheet" href="./assets/css/style.css" type="text/css">
</head>
<body>
<!-- search button -->

<nav class="navbar navbar-dark bg-dark navbar-expand-lg justify-content-center mb-4   ">
    <a class="navbar-brand text-danger">JoMa Pokédex </a>
    <img class="mr-2"  src="https://upload.wikimedia.org/wikipedia/en/3/39/Pokeball.PNG" width="30" height="30" alt="pikachu picture">
    <form class="form-inline" action="" method="get">
        <input id="input" class="form-control mr-sm-1 input-lg" type="text" name="search_txt" placeholder="Pokemon name or ID" aria-label="Search">
        <button id="button" class="btn btn-outline-success my-2 my-sm-0 btn-md" type="submit" name="search_btn">Search</button>
    </form>
</nav>

<!--  first part for image box-->
<div class="container rounded mt-5  ">
    <div class="row justify-content-center mt-5  ">
        <!-- 1 card -->
        <div class=" col-md-6  ">

            <div class="card border border-primary rounded shadow-lg p-3 mb-0 bg-light ">
                <img  id="pokemonpic" class="card-img icons" alt="pokemon picture" src="<?php echo $pokemon["sprite"] ?>" >
            </div>
        </div>

        <!-- second box for info of pokemon -->
        <div class=" col-md-6 ">
            <div class="card border border-primary rounded">
                <div class="card-body text-center">
                    <h5 class="card-title">Pokemon Information</h5>

                    <p class="card-text text-left font-weight-bold ">Name: <span id="firstname" class=" text-dark pokeinfo font-weight-normal text-capitalize ">
                            <?php echo $pokemon["name"] ?>
                        </span></p>
                    <p class="card-text text-left font-weight-bold">Id: <span id="firstid" class="text-dark pokeinfo font-weight-normal">
                            <?php echo $pokemon["id"] ?>
                        </span></p>
                    <p class="card-text text-left font-weight-bold">Weight: <span id="firstweight" class=" text-dark pokeinfo font-weight-normal">
                            <?php echo $pokemon["weight"] . " kg" ?>
                        </span></p>
                    <p class="card-text text-left font-weight-bold ">Moves: <br><span id="firstmoves" class=" text-dark pokeinfo font-weight-normal">
                            <?php echo implode(", ", $pokemon["moves"]) ?>
                        </span></p>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- arrow buttons-->
<nav  class= " mt-2  arrow" >
    <ul class="pagination  ">
        <li class="page-item"><a id="previous" class="page-link text-center" href="#">Previous</a></li>
        <li class="page-item"><a id="next" class="page-link text-center" href="#">Next</a></li>
    </ul>
</nav>
<div id="evolution"></div>

</body>
</html>