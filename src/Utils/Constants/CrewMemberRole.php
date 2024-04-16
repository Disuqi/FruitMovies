<?php

namespace App\Utils\Constants;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *   description="CrewMemberRole enum",
 *   type="string",
 *   title="CrewMemberRole",
 *   enum={"actor", "director"},
 *   example="actor"
 * )
 */
enum CrewMemberRole : string
{
    /**
     * @OA\Property(description="Represents an actor")
     */
    case Actor = "actor";

    /**
     * @OA\Property(description="Represents a director")
     */
    case Director = "director";
}