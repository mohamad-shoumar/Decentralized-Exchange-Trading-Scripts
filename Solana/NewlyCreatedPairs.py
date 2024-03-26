"Detect  New Pools Created on Solana Raydium DEX"

#MAnually see transactions of new pairs GThUX1Atko4tqhN2NaiTazWSeFWMuiUvfFnyJyUghFMJ under spl transfer section

from time import sleep
import logging
import pandas as pd
from datetime import datetime,timedelta
import os
from openpyxl import load_workbook

import asyncio
from typing import List, AsyncIterator, Tuple, Iterator
from asyncstdlib import enumerate
import time
import httpx
from typing import Any
from httpx import AsyncClient
from solders.pubkey import Pubkey
from solders.rpc.config import RpcTransactionLogsFilterMentions

from solana.rpc.websocket_api import connect
from solana.rpc.commitment import Finalized
from solana.rpc.api import Client
from solana.exceptions import SolanaRpcException
from websockets.exceptions import ConnectionClosedError, ProtocolError
from httpx import AsyncClient

# Type hinting imports
from solana.rpc.commitment import Commitment
from solana.rpc.websocket_api import SolanaWsClientProtocol
from solders.rpc.responses import RpcLogsResponse, SubscriptionResult, LogsNotification, GetTransactionResp
from solders.signature import Signature
from solders.transaction_status import UiPartiallyDecodedInstruction, ParsedInstruction

# Raydium Liquidity Pool V4
RaydiumLPV4 = "675kPX9MHTjS2zt1qfr1NYHuzeLXfQM9H24wFSUt1Mp8"
URI = "https://mainnet.helius-rpc.com/?api-key=b0030426-49da-4a2a-ab94-ef8961452c7c"  # "https://api.devnet.solana.com" | "https://api.mainnet-beta.solana.com"
WSS = "wss://mainnet.helius-rpc.com/?api-key=b0030426-49da-4a2a-ab94-ef8961452c7c"  # "wss://api.devnet.solana.com" | "wss://api.mainnet-beta.solana.com"
solana_client = Client(URI)
# Raydium function call name, look at raydium-amm/program/src/instruction.rs
log_instruction = "initialize2"
seen_signatures = set()


# Init logging
logging.basicConfig(filename='app.log', filemode='a', level=logging.DEBUG)
# Writes responses from socket to messages.json
# Writes responses from http req to  transactions.json
seen_tokens = []
filename = 'C:\\Users\\user\\Documents\\token_info.xlsx'

async def websocket_listener_task():
    async for websocket in connect(WSS):
        try:
            subscription_id = await subscribe_to_logs(
                websocket,
                RpcTransactionLogsFilterMentions(RaydiumLPV4),
                Finalized
            )
            async for i, signature in enumerate(process_messages(websocket, log_instruction)):  # type: ignore
                logging.info(f"{i=}")
                try:
                    get_tokens(signature, RaydiumLPV4)
                except Exception as e:
                    logging.exception(e)
                    sleep(5)
                    continue
        except Exception as e:
            logging.exception(e)
            continue
        except KeyboardInterrupt:
            if websocket:
                await websocket.logs_unsubscribe(subscription_id)
                break
            
async def main():
    listener_task = asyncio.create_task(websocket_listener_task())
    dexscreener_task = asyncio.create_task(call_dexscreener_api())
    await asyncio.gather(listener_task, dexscreener_task)


async def subscribe_to_logs(websocket: SolanaWsClientProtocol, 
                            mentions: RpcTransactionLogsFilterMentions,
                            commitment: Commitment) -> int:
    await websocket.logs_subscribe(
        filter_=mentions,
        commitment=commitment
    )
    first_resp = await websocket.recv()
    return get_subscription_id(first_resp)  # type: ignore


def get_subscription_id(response: SubscriptionResult) -> int:
    return response[0].result


async def process_messages(websocket: SolanaWsClientProtocol,
                           instruction: str) -> AsyncIterator[Signature]:
    """Async generator, main websocket's loop"""
    async for idx, msg in enumerate(websocket):
        value = get_msg_value(msg)
        if not idx % 100:
            pass
            # print(f"{idx=}")
        for log in value.logs:
            if instruction not in log:
                continue
            # Start logging
            logging.info(value.signature)
            logging.info(log)
            # Logging to messages.json
            with open("messages.json", 'a', encoding='utf-8') as raw_messages:  
                raw_messages.write(f"signature: {value.signature} \n")
                raw_messages.write(msg[0].to_json())
                raw_messages.write("\n ########## \n")
            # End logging
            yield value.signature


def get_msg_value(msg: List[LogsNotification]) -> RpcLogsResponse:
    return msg[0].result.value

def get_tokens(signature: Signature, RaydiumLPV4: Pubkey) -> None:
    """httpx.HTTPStatusError: Client error '429 Too Many Requests' 
    for url 'https://api.mainnet-beta.solana.com'
    For more information check: https://httpstatuses.com/429

    """
    global seen_signatures
    if signature in seen_signatures:
        # If we have already seen this signature, skip processing.
        logging.info(f"Duplicate transaction skipped: {signature}")
        return
    else:
        # Mark this signature as seen.
        seen_signatures.add(signature)
    try:
        # Attempt to get the transaction
        transaction = solana_client.get_transaction(
            signature,
            encoding='jsonParsed',
            max_supported_transaction_version=0
        )
        
        # Check if transaction is not None
        if transaction is None:
            logging.error(f'No transaction found for signature: {signature}')
            return
        
        # Start logging to transactions.json
        with open('transactions.json', 'a', encoding='utf-8') as raw_transactions:
            raw_transactions.write(f'signature: {signature}\\n')
            raw_transactions.write(transaction.to_json())        
            raw_transactions.write('\\n ########## \\n')
        # End logging
        
        instructions = get_instructions(transaction)
        filtered_instructions = instructions_with_program_id(instructions, RaydiumLPV4)
        logging.info(filtered_instructions)
        for instruction in filtered_instructions:
            tokens = get_tokens_info(instruction)
            print_table(tokens)
            print(f'True, https://solscan.io/tx/{signature}')

    except AttributeError as e:
        # Catching attribute errors if the 'transaction' is None or doesn't have the expected attributes
        logging.exception(f'AttributeError with signature {signature}: {e}')
    except httpx.HTTPStatusError as e:
        # Handling HTTPStatusError specifically for '429 Too Many Requests'
        if e.response.status_code == 429:
            logging.warning('429 Too Many Requests: The request is being rate limited.')
            # Implementing a basic exponential backoff strategy
            wait = 5  # Starting with a 1-minute backoff
            max_attempts = 5
            for i in range(max_attempts):
                time.sleep(wait)
                try:
                    # Retry the transaction fetch
                    transaction = solana_client.get_transaction(
                        signature,
                        encoding='jsonParsed',
                        max_supported_transaction_version=0
                    )
                    # If success, break out of the retry loop
                    if transaction is not None:
                        break
                except httpx.HTTPStatusError as e:
                    # If still getting '429', increase the wait time
                    wait *= 2
                    logging.warning(f'Retrying after {wait} seconds...')
                except Exception as e:
                    # Handle any other exceptions that occur during the retry
                    logging.exception(f'An unexpected error occurred: {e}')
                    break
            else:
                logging.error('Max retries reached, the transaction could not be fetched.')
    except Exception as e:
        # Catching all other exceptions
        logging.exception(f'An unexpected error occurred with signature {signature}: {e}')

def get_instructions(
    transaction: GetTransactionResp
) -> List[UiPartiallyDecodedInstruction | ParsedInstruction]:
    instructions = transaction \
                   .value \
                   .transaction \
                   .transaction \
                   .message \
                   .instructions
    return instructions


def instructions_with_program_id(
    instructions: List[UiPartiallyDecodedInstruction | ParsedInstruction],
    program_id: str
) -> Iterator[UiPartiallyDecodedInstruction | ParsedInstruction]:
    return (instruction for instruction in instructions
            if instruction.program_id == program_id)

def get_tokens_info(
    instruction: UiPartiallyDecodedInstruction | ParsedInstruction
) -> Tuple[Pubkey, Pubkey, Pubkey]:
    SOL_TOKEN_ADDRESS = "So11111111111111111111111111111111111111112"
    global seen_tokens
    accounts = instruction.accounts
    Pair = accounts[4]
    Token0 = accounts[8]
    Token1 = accounts[9]
    if str(Token0) == SOL_TOKEN_ADDRESS and str(Token1) not in seen_tokens:
        seen_tokens.append(str(Token1))
        logging.info(f"Token1 added to seen_tokens: {Token1}")
    elif str(Token0) != SOL_TOKEN_ADDRESS and str(Token0) not in seen_tokens:
        seen_tokens.append(str(Token0))
        logging.info(f"Token0 added to seen_tokens: {Token0}")
    print('lenghth seen_tokens: ', len(seen_tokens))

    # Start logging
    logging.info("find LP !!!")
    logging.info(f"\n Token0: {Token0}, \n Token1: {Token1}, \n Pair: {Pair}")
    # End logging
    return (Token0, Token1, Pair)



async def call_dexscreener_api():
    batch_size = 30
    update_interval = 30
    timestamp_offset = timedelta(seconds=0)
    while True:
        for i in range(0, len(seen_tokens), batch_size):
            current_batch = seen_tokens[i:i + batch_size]
            if current_batch:
                token_addresses = ','.join(str(token) for token in current_batch)
                url = f"https://api.dexscreener.com/latest/dex/tokens/{token_addresses}"
                print('url: ', url)
                try:
                    async with AsyncClient() as client:
                        response = await client.get(url)
                        response.raise_for_status()
                        data = response.json()
                        print(data)
                        for pair in data.get('pairs', []):
                            pair_data = {
                                'timestamp': datetime.now().isoformat(),
                                'DEX ID': pair.get('dexId'),
                                'Base Token Address': pair['baseToken'].get('address'),
                                'Base Token Symbol': pair['baseToken'].get('symbol'),
                                'Price USD': pair.get('priceUsd'),
                                'h24 buys (txn)': pair.get('txns', {}).get('h24', {}).get('buys'),
                                'h24 sells (txn)': pair.get('txns', {}).get('h24', {}).get('sells'),
                                'Ah24 buy-sells (txn)': 'd', 
                                'Volume 5m': pair.get('volume', {}).get('m5'),
                                'Price Change 5m': pair.get('priceChange', {}).get('m5'),
                                'fdv': pair.get('fdv'),
                                'Price Change H24': pair.get('priceChange', {}).get('h24'),
                                'Volume h24': pair.get('volume', {}).get('h24'),
                                'Liquidity USD': pair.get('liquidity', {}).get('usd'),
                            }
                            print("Appending data to Excel for pair:", pair_data)
                            append_to_excel(pair_data, filename)
                        # except Exception as e:
                        #     logging.exception(f'An unexpected error occurred: {e}')
                                                              
                        # timestamp_offset += timedelta(seconds=update_interval)  # Increment timestamp for the next entry     
                except Exception as e:
                    logging.error(f"Error fetching data from DexScreener: {e}")
                await asyncio.sleep(1)  
        
        await asyncio.sleep(10)

def print_table(tokens: Tuple[Pubkey, Pubkey, Pubkey]) -> None:
    data = [
        {'Token_Index': 'Token0', 'Account Public Key': tokens[0]},  # Token0
        {'Token_Index': 'Token1', 'Account Public Key': tokens[1]},  # Token1
        {'Token_Index': 'LP Pair', 'Account Public Key': tokens[2]}  # LP Pair
    ]
    print("============NEW POOL DETECTED====================")
    header = ["Token_Index", "Account Public Key"]
    print("│".join(f" {col.ljust(15)} " for col in header))
    print("|".rjust(18))
    for row in data:
        print("│".join(f" {str(row[col]).ljust(15)} " for col in header))

if __name__ == "__main__":
    RaydiumLPV4 = Pubkey.from_string(RaydiumLPV4)
    asyncio.run(main())
    
def append_to_excel(data, filepath, sheet_name='token_info'):
    try:
        print(f"Attempting to append data: {data}")
        df_new_row = pd.DataFrame([data])
        if not os.path.isfile(filepath):
            print(f"File {filepath} not found. Creating a new one.")
            with pd.ExcelWriter(filepath, engine='openpyxl') as writer:
                df_new_row.to_excel(writer, sheet_name=sheet_name, index=False)
        else:
            with pd.ExcelWriter(filepath, engine='openpyxl', mode='a', if_sheet_exists='overlay') as writer:
                writer.book = load_workbook(filepath)
                writer.sheets = dict((ws.title, ws) for ws in writer.book.worksheets)
                startrow = writer.book[sheet_name].max_row
                
                df_new_row.to_excel(writer, sheet_name=sheet_name, startrow=startrow, header=False, index=False)
                
                writer.book.save(filepath)
                writer.book.close()
                
            print(f"Data appended successfully to {filepath}")
    except Exception as e:
        print(f"Failed to append data: {e}")