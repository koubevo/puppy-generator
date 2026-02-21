<?php

return [
    'daily' => json_decode(env('EVENTS_JSON', '[]'), true),
];
