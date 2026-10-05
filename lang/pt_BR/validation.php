<?php
return [
 'required'=>'O campo :attribute é obrigatório.', 'email'=>'Informe um e-mail válido.', 'unique'=>'Este :attribute já está cadastrado.', 'confirmed'=>'A confirmação de :attribute não corresponde.', 'exists'=>'O valor selecionado para :attribute não existe.', 'in'=>'O valor selecionado para :attribute é inválido.', 'integer'=>'O campo :attribute deve ser um número inteiro.', 'string'=>'O campo :attribute deve ser um texto.', 'file'=>'O campo :attribute deve ser um arquivo.', 'mimes'=>'O anexo deve ser JPG, PNG, WEBP, PDF ou TXT.',
 'min'=>['string'=>'O campo :attribute deve ter pelo menos :min caracteres.'],
 'max'=>['string'=>'O campo :attribute deve ter no máximo :max caracteres.','file'=>'O arquivo deve ter no máximo :max KB.'],
 'password'=>['letters'=>'A senha deve conter pelo menos uma letra.','numbers'=>'A senha deve conter pelo menos um número.'],
 'attributes'=>['name'=>'nome','email'=>'e-mail','password'=>'senha','title'=>'assunto','description'=>'descrição','category_id'=>'categoria','priority'=>'prioridade','attachment'=>'anexo','body'=>'comentário','assigned_to'=>'responsável','role'=>'perfil','q'=>'busca'],
];
