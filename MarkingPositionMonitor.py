# Implement the class below, keeping the constructor's signature unchanged; it should take no arguments.
import json


class MarkingPositionMonitor:
    def __init__(self):
        # record order information to use for roll-back
        self.order = {}
        # record marking position for every symbol
        self.marking_pos = {}

    def on_event(self, message):
        # loads json data
        data = json.loads(message)
        event_type = data['type']
        if event_type == 'NEW':
            return self.new_order(data, data['side'] == 'BUY')
        if order_id not in self.order:
            return 0
        # invoke respect method according to event type
        return {
            'ORDER_ACK': no_change_marking_pos,
            'ORDER_REJECT': self.roll_back,
            'CANCEL': no_change_marking_pos,
            'CANCEL_ACK': self.roll_back,
            'CANCEL_REJECT': no_change_marking_pos,
            'FILL': self.fill
        }[event_type](data)

    def new_order(self, data, buy):
        symbol, quantity, order_id = data['symbol'], data['quantity'], data['order_id']
        # what if invalid input such as orderId already exist?
        if order_id not in self.order:
            if buy:
                self.order[order_id] = {'symbol': symbol, 'side': 'BUY', 'remain': quantity}
                self.marking_pos[symbol] = self.marking_pos.get(symbol, 0)
            else:
                self.order[order_id] = {'symbol': symbol, 'side': 'SELL', 'remain': quantity}
                # use dictionary.get() to avoid key error
                self.marking_pos[symbol] = self.marking_pos.get(symbol, 0) - quantity
            return self.marking_pos[symbol]

    # order_ack, cancel and cancel_rej doesn't change marking position
    def no_change_marking_pos(self, data):
        order_id = data['order_id']
        symbol = self.order[order_id]['symbol']
        return self.marking_pos[symbol]

    def roll_back(self, data):
        order_id = data['order_id']
        cur_order = self.order[order_id]
        symbol = cur_order['symbol']
        # only can change marking position which increased by SELL
        if 'rollback' not in cur_order and cur_order['side'] == 'SELL':
            cur_order['rollback'] = True
            self.marking_pos[symbol] += cur_order['remain']
        return self.marking_pos[symbol]

    def fill(self, data):
        order_id = data['order_id']
        filled = data['filled_quantity']
        cur_order = self.order[order_id]
        side, symbol = cur_order['side'], cur_order['symbol']
        filled = min(filled, cur_order['remain'])
        cur_order['remain'] -= filled
        if side == 'BUY':
            self.marking_pos[symbol] += filled
        return self.marking_pos[symbol]
