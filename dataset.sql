DELETE FROM `load`;
DELETE FROM `panel`;
DELETE FROM `battery`;
DELETE FROM `controller`;
DELETE FROM `inverter`;

ALTER TABLE `load`       AUTO_INCREMENT = 1;
ALTER TABLE `panel`      AUTO_INCREMENT = 1;
ALTER TABLE `battery`    AUTO_INCREMENT = 1;
ALTER TABLE `controller` AUTO_INCREMENT = 1;
ALTER TABLE `inverter`   AUTO_INCREMENT = 1;


INSERT INTO `load` (`name`, `description`, `power`, `price`, `stock`) VALUES ('Raspberry Pi A', 'Model A', 3.5, 25000.0, 10); 

INSERT INTO `load` (`name`, `description`, `power`, `price`, `stock`) VALUES ('Raspberry Pi B', 'Model B', 3.5, 30000.0, 10);

INSERT INTO `load` (`name`, `description`, `power`, `price`, `stock`) VALUES ('Raspberry Pi B+', 'Model B + 512 MB, full size HDMI, 4 USB ports, micro SD slot', 3.5, 35000.0, 10);

INSERT INTO `load` (`name`, `description`, `power`, `price`, `stock`) VALUES ('Lamp 7Watt', 'LED lamp, 7Watt', 7, 5000, 22);

INSERT INTO `load` (`name`, `description`, `power`, `price`, `stock`) VALUES ('Lamp 10Watt', 'LED lamp, 10Watt', 10, 7000, 5);

INSERT INTO `load` (`name`, `description`, `power`, `price`, `stock`) VALUES ('Monitor Chuangxin', 'Computer Monitor', 12, 15000.0, 2);

INSERT INTO `load` (`name`, `description`, `power`, `price`, `stock`) VALUES ('Monitor transtec', 'Computer Monitor', 10, 20000.0, 7);

INSERT INTO `load` (`name`, `description`, `power`, `price`, `stock`) VALUES ('Monitor Belinea', 'Computer Monitor', 9.5, 22000.0, 14);

INSERT INTO `load` (`name`, `description`, `power`, `price`, `stock`) VALUES ('Server black', 'New black server, passive cooling, dual core, suypport up to 20 Raspberries', 10, 120000.0, 4);

INSERT INTO `load` (`name`, `description`, `power`, `price`, `stock`) VALUES ('Switch Netgear', 'Switch, 8 ports', 8, 40000.0, 6);

INSERT INTO `load` (`name`, `description`, `power`, `price`, `stock`) VALUES ('Television', 'Small television, 18inch, CRT monitor', 20, 50000.0, 4);

INSERT INTO `load` (`name`, `description`, `power`, `price`, `stock`) VALUES ('Sound System Sony', 'Small sound system, max 85dB, bass', 50, 65000.0, 3);


INSERT INTO `panel` (`name`, `description`, `power`, `peak_power`, `price`, `stock`) VALUES ('Panel 120W', 'Old Panel', 120, 130, 100000, 2);

INSERT INTO `panel` (`name`, `description`, `power`, `peak_power`, `price`, `stock`) VALUES ('Panel 200W', 'New Panel, cheaper', 200, 220, 60000, 32);

INSERT INTO `panel` (`name`, `description`, `power`, `peak_power`, `price`, `stock`) VALUES ('Panel 300W', 'Future Panel, more powerful', 300, 350, 80000, 0);


INSERT INTO `battery` (`name`, `description`, `dod`, `voltage`, `loss`, `discharge`, `lifespan`, `capacity`, `price`, `stock`, `max_const_current`, `max_peak_current`, `avg_const_current`, `max_humidity`, `max_temperature`) VALUES ('Battery ACP 80Ah', 'Lead battery, 40kg, 80Ah', 0.5, 12.8, 0.2, 0.1, 500, 80, 50000, 3, 300, 3000, 30, 50, 25);

INSERT INTO `battery` (`name`, `description`, `dod`, `voltage`, `loss`, `discharge`, `lifespan`, `capacity`, `price`, `stock`, `max_const_current`, `max_peak_current`, `avg_const_current`, `max_humidity`, `max_temperature`) VALUES ('Battery LFT 100Ah', 'LiFePO4 Battery, 15kg, 100Ah', 0.85, 12.8, 0.01, 0.03, 2000, 100, 250000, 10, 300, 3000, 30, 75, 65);

INSERT INTO `battery` (`name`, `description`, `dod`, `voltage`, `loss`, `discharge`, `lifespan`, `capacity`, `price`, `stock`, `max_const_current`, `max_peak_current`, `avg_const_current`, `max_humidity`, `max_temperature`) VALUES ('Battery LFT 100Ah W', 'Winston LiFeYPO4 Battery, 15kg, 100Ah', 0.85, 12.8, 0.005, 0.03, 3000, 100, 275000, 4, 300, 3000, 30, 80, 65);


INSERT INTO `inverter` (`name`, `description`, `loss`, `voltage`, `max_current`, `price`, `stock`) VALUES ('Inverter standart', 'normal inverter', 0.01,12.8,10,15000,10);

INSERT INTO `inverter` (`name`, `description`, `loss`, `voltage`, `max_current`, `price`, `stock`) VALUES ('Inverter deluxe', 'Inverter with LCD display and usage statistics', 0.015,12.5,20,20000,5);


INSERT INTO `controller` (`name`, `description`, `loss`, `price`, `stock`, `voltage`, `max_current`) VALUES ('Controller C2415', 'Basic controller',0.01 , 10000 , 5, 12 , 10);

INSERT INTO `controller` (`name`, `description`, `loss`, `price`, `stock`, `voltage`, `max_current`) VALUES ('Mind controller', 'Basic mind controller. Hail the Hypnotoad!',0.00 , 100000 , 100, 12 , 15);



