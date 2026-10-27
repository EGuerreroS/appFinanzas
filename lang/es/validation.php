<?php

// Mensajes de validación en español (solo las reglas que usa la app; el resto cae en inglés)
return [
    'between' => [
        'numeric' => 'El campo :attribute debe estar entre :min y :max.',
    ],
    'date' => 'El campo :attribute no es una fecha válida.',
    'email' => 'El campo :attribute debe ser un correo válido.',
    'exists' => 'El valor seleccionado en :attribute no es válido.',
    'gt' => [
        'numeric' => 'El campo :attribute debe ser mayor que :value.',
    ],
    'in' => 'El valor seleccionado en :attribute no es válido.',
    'integer' => 'El campo :attribute debe ser un número entero.',
    'max' => [
        'numeric' => 'El campo :attribute no debe ser mayor que :max.',
        'string' => 'El campo :attribute no debe tener más de :max caracteres.',
    ],
    'min' => [
        'numeric' => 'El campo :attribute debe ser al menos :min.',
    ],
    'numeric' => 'El campo :attribute debe ser un número.',
    'regex' => 'El formato de :attribute no es válido.',
    'required' => 'El campo :attribute es obligatorio.',
    'string' => 'El campo :attribute debe ser texto.',
    'unique' => 'Ya existe un registro con ese :attribute.',

    'attributes' => [
        'email' => 'correo',
        'password' => 'contraseña',
    ],
];
