#! /usr/bin/env python

from smartcard.System import readers

from smartcard.CardMonitoring import CardMonitor, CardObserver
from smartcard.util import toHexString
from time import sleep
import sys
import os

voice = "-ven-us+f3 -s150"

class PrintObserver(CardObserver):
	def update(self, observable, actions):
        	(addedcards, removedcards) = actions
        	for card in addedcards:
            		print (toHexString(card.atr))

def espeak_func (txt):
	os.system('sudo su - pi -c \'' + txt + '\'')

# define the APDUs used in this script
#SELECT = [0x00, 0xA4, 0x04, 0x00, 0x0A, 0xA0, 0x00, 0x00, 0x00, 0x62,
#    0x03, 0x01, 0x0C, 0x06, 0x01]
#COMMAND = [0xFF,0xCA,0x00,0x00,0x00]

# get all the available readers


espeaktxt = "espeak " + voice + "\"Please insert card. Thank you!\""
espeak_func(espeaktxt)


r = readers()

reader = r[int(sys.argv[1])]

connection = reader.createConnection()
connection.connect()

cardmonitor = CardMonitor()
cardobserver = PrintObserver()
cardmonitor.addObserver(cardobserver)

sleep(3)

# don't forget to remove observer, or the
# monitor will poll forever...
cardmonitor.deleteObserver(cardobserver)

#data, sw1, sw2 = connection.transmit(SELECT)
#print data
#print "Select Applet: %02X %02X" % (sw1, sw2)

#data, sw1, sw2 = connection.transmit(COMMAND)
#print data
#print "Command: %02X %02X" % (sw1, sw2)