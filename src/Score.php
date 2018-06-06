<?php

// ======================
// Archives World Map
//
// Class: Score
// ======================

class Score {

	public static function calc($db, $iduser){
        
        // Points from "Add an Institution"
        $db->exec(
            'SELECT id FROM Users_Institutions ' .
            'WHERE iduser = ' . $iduser
        );
        $add_an_institution = $db->count();
        
		// Calculate Score
        $score = 0;
        $score += $add_an_institution * 0.1;
        
        return $score;
	}
}
