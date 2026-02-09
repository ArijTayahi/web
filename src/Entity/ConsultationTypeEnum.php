<?php

namespace App\Entity;

enum ConsultationTypeEnum: string
{
    case IN_PERSON = 'IN_PERSON'; //cabinet
    case ONLINE = 'ONLINE';       // EN_LIGNE
}
