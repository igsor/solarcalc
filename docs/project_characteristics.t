
Project characteristics
=======================

Sanity checks / constraints
---------------------------

The following questions have to be answered:

1. Do we produce enough energy to power the system?
2. Can we store enough energy to go through the night?
3. Can we handle the peak power?
4. Are the currents in the allowed range?
5. Are the voltages balanced?

We can use the following values for that:

1. panel energy >= consumed energy (day) + battery consumed energy
2. Unused battery capacity >= 0
3. Peak consumed power (day) <= total panel power
4. max( max. current (day), max. current (night) ) < current limit
5. panel voltage == battery voltage == load voltage

Noteworthy data
---------------

Sanity checks
~~~~~~~~~~~~~

* Total panel energy (+ Consumed energy (day))
* Consumed energy (night) (-> Battery consumed energy)
* Unused battery capacity (+ Peak consumed power (day))
* Max. current

Panel data
~~~~~~~~~~

* Total panel power
* Total panel energy

Battery data
~~~~~~~~~~~~

* Total battery capacity
* Consumed battery capacity
* Unused battery capacity
* Battery input energy (-> Battery usable energy)
* Battery overcapacity

Energy balance
~~~~~~~~~~~~~~

* Total panel energy
* Consumed energy (day)
* Consumed energy (night) (-> Battery consumed energy)

Reserves
~~~~~~~~

* Unused battery capacity
* Panel reserve

Time data
~~~~~~~~~

* Min/Max/Avg time until charged
* Battery discharge time

Conclusion
----------

* Total panel power
* Panel reserve (power)
* Peak consumed power (day)
* Peak consumed power (night)

* Total panel energy
* Consumed energy (day)
* Consumed energy (night) (-> Battery consumed energy)
* Battery input energy (-> Battery usable energy)

* Total battery capacity
* Consumed battery capacity
* Unused battery capacity
* Battery overcapacity

* Max. current (charge)
* Max. current (day)
* Max. current (night)

* Min/Max/Avg time until charged
* Battery discharge time

