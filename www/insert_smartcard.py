#! /usr/bin/env python

from __future__ import print_function
from time import sleep

from smartcard.CardMonitoring import CardMonitor, CardObserver
from smartcard.util import toHexString

class PrintObserver(CardObserver):
    def update(self, observable, actions):
        (addedcards, removedcards) = actions
        for card in addedcards:
            print (toHexString(card.atr))

if __name__ == '__main__':
    cardmonitor = CardMonitor()
    cardobserver = PrintObserver()
    cardmonitor.addObserver(cardobserver)
    sleep(3)
    
    cardmonitor.deleteObserver(cardobserver)