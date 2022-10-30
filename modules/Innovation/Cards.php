<?php

namespace Innovation;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception as DbException;
use Innovation\Errors\CardNotFoundException;
use Innovation\Models\Card;

/**
 * Service class for finding Cards in the database. Also illustrates inversion of control and how to pass the
 * database connection using dependency injection. This separates out of the main Innovation class the logic for
 * dealing with Cards, allowing DB queries around cards to be located here, and then returning model objects for each
 * card.
 *
 * @see \Innovation\Models\Card
 */
class Cards
{
    private Connection $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * Return a card given an ID
     *
     * @param int $id
     * @return Card
     * @throws CardNotFoundException
     */
    public function find(int $id): Card
    {
        $card = null;
        try {
            $card = $this->db->fetchAssociative("SELECT * FROM card WHERE id = ?", [$id]);
        } catch (DbException $e) {
            // TODO: add a logger class to log this error, this is an exceptional case
        }
        if ($card) {
            return new Card($card);
        } else {
            throw new CardNotFoundException("Failed to find card with id $id");
        }
    }
}
