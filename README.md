# Moduł: categoryblocks

Moduł pozwalający wybrać 2 kategorie oraz wyświetlić do 10 produktów należących do nich, jeśli:
- są włączone (aktywne)
- ich stan magazynowy jest większy od 0

Obie kategorie muszą być wybrane w konfiguracji modułu oraz posiadać co najmniej 1 produkt kwalifikujący się do wyświetlenia. W przeciwnym razie moduł nie zwróci żadnych danych na froncie.

---

Po instalacji modułu należy go przemieścić w dowolne miejsce. W celu testowym polecam wykorzystać hook displayHome.

Konfiguracja BackOffice:
![image](https://user-images.githubusercontent.com/8435019/134585460-61c83fe8-3405-4e9b-8ee3-f498dc919a12.png)

Wygląd FrontOffice:
![image](https://user-images.githubusercontent.com/8435019/134585370-eada890d-d8e5-4aae-a2b5-ec1e97467323.png)

---

Tworzone na wersjach:
- PrestaShop: 1.7.7.7
- PHP: 7.3
