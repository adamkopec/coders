# Wyodrębnienie klas

Pobierz zamówienia                      | źródło danych zamówień (OrderService_DataSource)
(skąd? jako tablicę, czy iterator?)     |
 - filtrując je według dat              | filtr źródła (OrderService_DataSource_Filter), potencjalnie więcej niż jeden

Jeśli zwrócono zero zamówień            | ?
- zwróć komunikat o błędzie             |

Ustaw sumę wartości                     | usługa (szczegóły implementacji)
i licznik na zero

Dla każdego zamówienia                  | usługa
    - pobierz wartość (netto, brutto?)  |
    - dodaj wartość do sumy
    - zwiększ licznik                   |

Podziel sumę przez ilość                | usługa
zamówień i zwróć wynik