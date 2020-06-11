#  CRIAÇÃO DO AMBIENTE

- clonar o projetxo
- docker-compose up -d
- Acessar o mysql (127.0.0.1:3306, usuario root, senha natue) e rodar o sql ./data/sql/development_wms.sql no schema development_wms

#CONFIGURAÇÕES

- ACESSAR o container wms_server -> docker exec -it wms_server bash
- npm install - Nao deve ter erros
- composer install
   - Nao deve ter erros
   - Depois de instalar as 90 dependencias, ele começará a configurar as variaveis de ambiente, alterar as configuracoes abaixo
     