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
        
        // Points from "Write a new thread"
        $db->exec(
            'SELECT id FROM Community ' .
            'WHERE parent_id IS NULL AND iduser = ' . $iduser
        );
        $write_a_thread = $db->count();
        
        // Points from "Reply in a thread"
        $db->exec(
            'SELECT id FROM Community ' .
            'WHERE parent_id IS NOT NULL AND iduser = ' . $iduser
        );
        $write_a_reply = $db->count();
        
		// Calculate Score
        $score = 0;
        $score += $add_an_institution * 0.7;
        $score += $write_a_thread * 0.3;
        $score += $write_a_reply * 0.1;
        
        return $score;
	}
}
