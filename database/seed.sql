use pothenesxes;

INSERT INTO users (first_name, last_name, email, phone, role)
Values ( 'Admin' , 'Admin' , 'admin@test.com' , '00000000' , 'Admin'),
('Paraskevas' , 'Vafeiadis' , 'paras@gmail.com' , '99004300' , 'User'),
('Nikos' , 'Christodoulidis' , 'nikos@gmail.com' , '99999999' , 'Politician');

INSERT INTO accounts(user_id,username,password_hash)
VALUES('1' , 'admin' , '$2a$12$5KDPQ8zFGGIJEe8Ba9pu.uMsuGbiA0qcrN8t8HhvHLr/YArlHv64S'), /*password is "admin123"*/
('2' , 'test' , '$2a$12$A04wG/KZVZRhFQ5kYoD3SOpaI/2GET.dhKB73Cgr0hwLOGi2Nj7iq'),/*password is "test123"*/
('3' , 'Nikaros' , '$2a$12$3ruv8Y.0WbfGYbTulhbTLuiRzzWGlM2p7UPhiT/tglribZBjIPH8m'); /*password is "pafos1234"*/

INSERT INTO positions(position_id,position_name)
VALUES 
('1' , 'President'),
('2' , 'Member of Parliament'),
('3' , 'Member of European parliament'); 

