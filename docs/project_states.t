
Project status State machine
============================

    States:
        * Planned   (P)
        * Executing (E)
        * Completed (C)
        * Cancelled (X)

    Transition graph:

        P <--1--> E <----> C
        ^         |
        |         |
        v         |
        X <--2----+

    State editability:

            Metadata    Configuration   Deletable
            ----------- --------------- ---------
        P   Yes         Yes             Yes
        E   Yes         No              No
        C   No          No              No
        X   No          No              Yes

    Transition actions:

        1) P -> E: Remove project amount from stock
           E -> P: Add project amount to stock
        2) E -> X: Add project amount to stock


