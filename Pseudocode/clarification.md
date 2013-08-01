# Standaryzacja i stabilizacja koncepcji

Pobierz zamówienia jako tablicę         | źródło danych zamówień (DataSource)
 - filtrując je według dat              | filtry źródła (DataSource_Filter) podpinane pod źródło, standardowo
                                        | dwa: Filter_DateGreaterThan, Filter_DateLessThan
Jeśli zwrócono zero zamówień            | usługa: isOrderCountPositive()
- zwróć komunikat o błędzie             |

Ustaw sumę wartości                     | __construct(), zmienne prywatne sum i counter
i licznik na zero

Dla każdego zamówienia                  | countAverage()
    - pobierz wartość (netto, brutto?)  | $value = getValue(Order)
    - dodaj wartość do sumy             | addToSum($value)
    - zwiększ licznik                   | incrementCounter()

Podziel sumę przez ilość                | countAverage()
zamówień i zwróć wynik