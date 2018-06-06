<?php

// ======================
// Archives World Map
//
// Class: Institutions
// ======================

class Institutions {

	public function pins($query){
		
		foreach ($query as $pin){
            $pins .= PHP_EOL . 'var marker' . 
            $pin['id'] . ' = L.marker([' . 
            $pin['latitude'] . ',' . 
            $pin['longitude'] . ']).addTo(mymap)'.
                '.bindPopup(\'' . html_entity_decode($pin['name']) . 
                '<br><a href=\"./info/' . $pin['id'] . '\">info</a>' . '\');';
        }
        
        return $pins;
	}
}
