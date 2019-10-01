<?php
    $searchRequest = "7";

    //var_dump($pokemon["moves"][0]["move"]);
    //echo $pokemon["moves"][0]["move"]["name"];

    function getPokemon(string $searchRequest) : array {
        $apiUrl = "https://pokeapi.co/api/v2/pokemon/$searchRequest";
        $json = file_get_contents($apiUrl);
        $pokemonInfo = json_decode($json, true);
        $pokemon = ["id", "name", "moves", "species", "sprite"];
        $pokemon["id"] = $pokemonInfo["id"];
        $pokemon["name"] = $pokemonInfo["name"];
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
        $chain[] = $info["chain"]["species"]["name"];

        foreach ($info["chain"]["evolves_to"] as $secondEvoSpecies){
            //var_dump($species["species"]["name"]);
            $chain[] = $secondEvoSpecies["species"]["name"];
            foreach ($secondEvoSpecies["evolves_to"] as $finalEvoSpecies){
                $chain[] = $finalEvoSpecies["species"]["name"];
            }
        }
        //return $info["chain"];
        //return $info["chain"]["evolves_to"][0]["evolves_to"][0]["species"]["name"];
        return  $chain; //return evolution chain, first element will always be the originator of the chain
    }

    var_dump(getPokemon($searchRequest));

    ?>

<html>
<body>
<p>
    <?php
        echo getEvolutions("https://pokeapi.co/api/v2/pokemon-species/2/");
    ?>
</p>
</body>
</html>