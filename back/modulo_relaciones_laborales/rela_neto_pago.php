<?php
require_once '../sistema_global/conexion.php';
require_once '../sistema_global/session.php';

// header json
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$cedula = $data['cedula'];





// Estructura de los datos a enviar
$data = [
    "2023" => [
        "enero" => [
            "conceptos" => [
                "Prima por hijo" => [
                    'cod'=> '002',
                    "tipo" => "A",
                    "monto" => 100
                ],
                "RPE" => [
                    'cod'=> '009',
                    "tipo" => "D",
                    "monto" => 20
                ],
                "sueldo_base" => [
                    'cod'=> '001',
                    "tipo" => "A",
                    "monto" => 800
                ]
            ],
            "integral" => 880
        ],
        "febrero" => [
            "conceptos" => [
                "Prima por hijo" => [
                    'cod'=> '002',
                    "tipo" => "A",
                    "monto" => 100
                ],
                "RPE" => [
                    'cod'=> '009',
                    "tipo" => "D",
                    "monto" => 20
                ],
                "sueldo_base" => [
                    'cod'=> '001',
                    "tipo" => "A",
                    "monto" => 800
                ]
            ],
            "integral" => 880
        ],
        "marzo" => [
            "conceptos" => [
                "Prima por hijo" => [
                    'cod'=> '002',
                    "tipo" => "A",
                    "monto" => 100
                ],
                "RPE" => [
                    'cod'=> '009',
                    "tipo" => "D",
                    "monto" => 20
                ],
                 "sueldo_base" => [
                    'cod'=> '001',
                    "tipo" => "A",
                    "monto" => 800
                ]
            ],
            "integral" => 880
        ],
        "abril" => [
            "conceptos" => [
                "Prima por hijo" => [
                    'cod'=> '002',
                    "tipo" => "A",
                    "monto" => 100
                ],
                "RPE" => [
                    'cod'=> '009',
                    "tipo" => "D",
                    "monto" => 20
                ],
                 "sueldo_base" => [
                    'cod'=> '001',
                    "tipo" => "A",
                    "monto" => 800
                ]
            ],
            "integral" => 880
        ],
        "mayo" => [
            "conceptos" => [
                "Prima por hijo" => [
                    'cod'=> '002',
                    "tipo" => "A",
                    "monto" => 100
                ],
                "RPE" => [
                    'cod'=> '009',
                    "tipo" => "D",
                    "monto" => 20
                ],
                 "sueldo_base" => [
                    'cod'=> '001',
                    "tipo" => "A",
                    "monto" => 800
                ]
            ],
            "integral" => 880
        ],
        "junio" => [
            "conceptos" => [
                "Prima por hijo" => [
                    'cod'=> '002',
                    "tipo" => "A",
                    "monto" => 100
                ],
                "RPE" => [
                    'cod'=> '009',
                    "tipo" => "D",
                    "monto" => 20
                ],
                 "sueldo_base" => [
                    'cod'=> '001',
                    "tipo" => "A",
                    "monto" => 800
                ]
            ],
            "integral" => 880
        ],
        "julio" => [
            "conceptos" => [
                "Prima por hijo" => [
                    'cod'=> '002',
                    "tipo" => "A",
                    "monto" => 100
                ],
                "RPE" => [
                    'cod'=> '009',
                    "tipo" => "D",
                    "monto" => 20
                ],
                 "sueldo_base" => [
                    'cod'=> '001',
                    "tipo" => "A",
                    "monto" => 800
                ]
            ],
            "integral" => 880
        ],
        "agosto" => [
            "conceptos" => [
                "Prima por hijo" => [
                    'cod'=> '002',
                    "tipo" => "A",
                    "monto" => 100
                ],
                "RPE" => [
                    'cod'=> '009',
                    "tipo" => "D",
                    "monto" => 20
                ],
                 "sueldo_base" => [
                    'cod'=> '001',
                    "tipo" => "A",
                    "monto" => 800
                ]
            ],
            "integral" => 880
        ],
        "septiembre" => [
            "conceptos" => [
                "Prima por hijo" => [
                    'cod'=> '002',
                    "tipo" => "A",
                    "monto" => 100
                ],
                "RPE" => [
                    'cod'=> '009',
                    "tipo" => "D",
                    "monto" => 20
                ],
                 "sueldo_base" => [
                    'cod'=> '001',
                    "tipo" => "A",
                    "monto" => 800
                ]
            ],
            "integral" => 880
        ],
        "octubre" => [
            "conceptos" => [
                "Prima por hijo" => [
                    'cod'=> '002',
                    "tipo" => "A",
                    "monto" => 100
                ],
                "RPE" => [
                    'cod'=> '009',
                    "tipo" => "D",
                    "monto" => 20
                ],
                 "sueldo_base" => [
                    'cod'=> '001',
                    "tipo" => "A",
                    "monto" => 800
                ]
            ],
            "integral" => 880
        ],
        "noviembre" => [
            "conceptos" => [
                "Prima por hijo" => [
                    'cod'=> '002',
                    "tipo" => "A",
                    "monto" => 100
                ],
                "RPE" => [
                    'cod'=> '009',
                    "tipo" => "D",
                    "monto" => 20
                ],
                 "sueldo_base" => [
                    'cod'=> '001',
                    "tipo" => "A",
                    "monto" => 800
                ]
            ],
            "integral" => 880
        ],
        "diciembre" => [
            "conceptos" => [
                "Prima por hijo" => [
                    'cod'=> '002',
                    "tipo" => "A",
                    "monto" => 100
                ],
                "RPE" => [
                    'cod'=> '009',
                    "tipo" => "D",
                    "monto" => 20
                ],
                 "sueldo_base" => [
                    'cod'=> '001',
                    "tipo" => "A",
                    "monto" => 800
                ]
            ],
            "integral" => 880
        ]
    ],
    "2024" => [
        "enero" => [
            "conceptos" => [
                "Prima por hijo" => [
                    'cod'=> '002',
                    "tipo" => "A",
                    "monto" => 100
                ],
                "RPE" => [
                    'cod'=> '009',
                    "tipo" => "D",
                    "monto" => 20
                ],
                 "sueldo_base" => [
                    'cod'=> '001',
                    "tipo" => "A",
                    "monto" => 800
                ]
            ],
            "integral" => 880
        ],
        "febrero" => [
            "conceptos" => [
                "Prima por hijo" => [
                    'cod'=> '002',
                    "tipo" => "A",
                    "monto" => 100
                ],
                "RPE" => [
                    'cod'=> '009',
                    "tipo" => "D",
                    "monto" => 20
                ],
                 "sueldo_base" => [
                    'cod'=> '001',
                    "tipo" => "A",
                    "monto" => 800
                ]
            ],
            "integral" => 880
        ],
        "marzo" => [
            "conceptos" => [
                "Prima por hijo" => [
                    'cod'=> '002',
                    "tipo" => "A",
                    "monto" => 100
                ],
                "RPE" => [
                    'cod'=> '009',
                    "tipo" => "D",
                    "monto" => 20
                ],
                 "sueldo_base" => [
                    'cod'=> '001',
                    "tipo" => "A",
                    "monto" => 800
                ]
            ],
            "integral" => 880
        ],
        "abril" => [
            "conceptos" => [
                "Prima por hijo" => [
                    'cod'=> '002',
                    "tipo" => "A",
                    "monto" => 100
                ],
                "RPE" => [
                    'cod'=> '009',
                    "tipo" => "D",
                    "monto" => 20
                ],
                 "sueldo_base" => [
                    'cod'=> '001',
                    "tipo" => "A",
                    "monto" => 800
                ]
            ],
            "integral" => 880
        ],
        "mayo" => [
            "conceptos" => [
                "Prima por hijo" => [
                    'cod'=> '002',
                    "tipo" => "A",
                    "monto" => 100
                ],
                "RPE" => [
                    'cod'=> '009',
                    "tipo" => "D",
                    "monto" => 20
                ],
                 "sueldo_base" => [
                    'cod'=> '001',
                    "tipo" => "A",
                    "monto" => 800
                ]
            ],
            "integral" => 880
        ],
        "junio" => [
            "conceptos" => [
                "Prima por hijo" => [
                    'cod'=> '002',
                    "tipo" => "A",
                    "monto" => 100
                ],
                "RPE" => [
                    'cod'=> '009',
                    "tipo" => "D",
                    "monto" => 20
                ],
                 "sueldo_base" => [
                    'cod'=> '001',
                    "tipo" => "A",
                    "monto" => 800
                ]
            ],
            "integral" => 880
        ]
    ]
];

echo json_encode($data);


$conexion->close();