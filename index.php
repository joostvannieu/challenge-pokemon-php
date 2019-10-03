<?php
    declare(strict_types=1);
    define("API_URL", "https://pokeapi.co/api/v2/pokemon/");

    $pokemon = getPokemon(getSearchRequest());

    function getSearchRequest() : string {
        if (isset($_GET["search_btn"])) { //check if form was submitted
            if ($_GET["search_txt"] == null)
                $searchRequest = "1";
            else
                $searchRequest = $_GET["search_txt"]; //get input text
        } else
            $searchRequest = "1";
        return strtolower($searchRequest);
    }

    function getPokemon(string $searchRequest) : array {
        $apiUrl = API_URL.$searchRequest;
        if (file_get_contents($apiUrl) == null){
            $apiUrl = API_URL . "1";
        }
        $json = file_get_contents($apiUrl);
        $pokemonInfo = json_decode($json, true);
        $pokemon = ["id", "name", "weight", "moves", "species", "sprite"];
        $pokemon["id"] = $pokemonInfo["id"];
        $pokemon["name"] = $pokemonInfo["name"];
        $pokemon["weight"] = $pokemonInfo["weight"]/10;
        $pokemon["moves"] = getMoves($pokemonInfo["moves"]);
        $pokemon["sprite"] = $pokemonInfo["sprites"]["front_default"];
        $pokemon["species"] = getEvolutions($pokemonInfo["species"]["url"]);
        return $pokemon;
    }

    function getEvoPoke(string $name) : array {
        $apiUrl = API_URL.$name;
        $json = file_get_contents($apiUrl);
        $evoPokeInfo = json_decode($json, true);
        $evoPoke = ["id", "name", "sprite"];
        $evoPoke["id"] = $evoPokeInfo["id"];
        $evoPoke["name"] = $evoPokeInfo["name"];
        $evoPoke["sprite"] = $evoPokeInfo["sprites"]["front_default"];
        return $evoPoke;
    }

    function getEvolutions(string $url) : array {
        $prefix = "evo-";
        $a = $b = $c = 0;
        $json = file_get_contents($url);
        $info = json_decode($json, true);
        $json = file_get_contents($info["evolution_chain"]["url"]);
        $info = json_decode($json, true);
        $chain = [];
        $chain[] = ["tree" => $prefix.$a, "pokemon" => getEvoPoke($info["chain"]["species"]["name"])];

        foreach ($info["chain"]["evolves_to"] as $secondEvoSpecies){
            $c = 0;
            $chain[] = ["tree" => $prefix.$a.$b, "pokemon" => getEvoPoke($secondEvoSpecies["species"]["name"])];
            foreach ($secondEvoSpecies["evolves_to"] as $finalEvoSpecies){
                $chain[] = ["tree" => $prefix.$a.$b.$c, "pokemon" => getEvoPoke($finalEvoSpecies["species"]["name"])];
                $c++;
            }
            $b++;
        }
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
                $randomIndex = random_int(0, count($moves)-1);
                $tempMoves[] = $moves[$randomIndex]["move"]["name"];
                unset($moves[$randomIndex]);
            }
            return $tempMoves;
        }
    }

    function showEvolutions(array $pokemon) : void {
        foreach ($pokemon["species"] as $species){
            $id = $species["pokemon"]["id"];
            $name = ucfirst($species["pokemon"]["name"]);
            $url = $species["pokemon"]["sprite"];
            echo "<a href='http://pokemon.php/index.php?search_txt=$id&search_btn='>
                    <img src=$url alt=$name title=$name>
                  </a>";
        }
    }

    function showEvoChain(array $pokemon) : void {
        echo "<table>";
        foreach ($pokemon["species"] as $species){
            $name = ucfirst($species["pokemon"]["name"]);
            $chainPosition = $species["tree"];
            if (strlen($chainPosition) == 5)
                echo "<tr><td>" . $name . "</td></tr><tr>";
            elseif (strlen($chainPosition) == 6)
                echo "<td>" . $name . "</td>";
        }
        echo "</tr></table>";
    }

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <title>Pokédex</title>
        <!-- Google font -->
        <link href="https://fonts.googleapis.com/css?family=Exo+2&display=swap" rel="stylesheet">
        <!-- Bootstrap and other CSS -->
        <link rel="stylesheet" href="./assets/css/bootstrap.css" type="text/css">
        <link rel="stylesheet" href="./assets/css/style.css" type="text/css">
    </head>
    <body>
        <!-- Navbar -->
        <nav class="navbar navbar-dark bg-dark navbar-expand-lg justify-content-center mb-4   ">
            <a class="navbar-brand text-danger">JoMa Pokédex </a>
            <img class="mr-2"  src="https://upload.wikimedia.org/wikipedia/en/3/39/Pokeball.PNG" width="30" height="30" alt="pikachu picture">
            <!-- Search -->
            <form class="form-inline" action="" method="get">
                <input id="input" class="form-control mr-sm-1 input-lg" type="text" name="search_txt" placeholder="Pokemon name or ID" aria-label="Search">
                <button id="button" class="btn btn-outline-success my-2 my-sm-0 btn-md" type="submit" name="search_btn">Search</button>
            </form>
        </nav>
        <!-- END Navbar -->
        <!--  info boxes -->
        <div class="container rounded mt-5  ">
            <div class="row justify-content-center mt-5  ">
                <!-- first box for picture of pokemon -->
                <div class=" col-md-6  ">
                    <div class="card border border-primary rounded shadow-lg p-3 mb-0 bg-light ">
                        <img  id="pokemonpic" alt="pokemon picture" src="<?php echo $pokemon["sprite"] ?>" >
                    </div>
                </div>
                <!-- second box for info of pokemon -->
                <div class=" col-md-6 ">
                    <div class="card border border-primary rounded">
                        <div class="card-body text-center">
                            <h5 class="card-title">Pokemon Information</h5>
                            <p class="card-text text-left font-weight-bold ">Name:
                                <span id="firstname" class=" text-dark pokeinfo font-weight-normal text-capitalize ">
                                    <?php echo $pokemon["name"] ?>
                                </span>
                            </p>
                            <p class="card-text text-left font-weight-bold">Id:
                                <span id="firstid" class="text-dark pokeinfo font-weight-normal">
                                    <?php echo $pokemon["id"] ?>
                                </span>
                            </p>
                            <p class="card-text text-left font-weight-bold">Weight:
                                <span id="firstweight" class=" text-dark pokeinfo font-weight-normal">
                                    <?php echo $pokemon["weight"] . " kg" ?>
                                </span>
                            </p>
                            <p class="card-text text-left font-weight-bold ">Moves: <br>
                                <span id="firstmoves" class=" text-dark pokeinfo font-weight-normal">
                                    <?php echo implode(", ", $pokemon["moves"]) ?>
                                </span>
                            </p>
                            <p class="card-text text-left font-weight-bold ">Evolutions: <br>
                                <?php showEvolutions($pokemon) ?>
                            </p>
                            <p class="card-text text-left font-weight-bold ">Family tree: <br>
                                <?php showEvoChain($pokemon) ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- END info boxes -->
        <!-- prev and next buttons-->
        <nav  class= " mt-2  arrow" >
            <ul class="pagination  ">
                <li class="page-item"><a id="previous" class="page-link text-center"
                                         href="http://pokemon.php/index.php?search_txt=<?php echo $pokemon["id"]-1 ?>&search_btn=">Previous</a></li>
                <li class="page-item"><a id="next" class="page-link text-center"
                                         href="http://pokemon.php/index.php?search_txt=<?php echo $pokemon["id"]+1 ?>&search_btn=">Next</a></li>
            </ul>
        </nav>
        <!-- END prev and next buttons-->
    </body>
</html>