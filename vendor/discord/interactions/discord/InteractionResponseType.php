<?php

namespace Discord;

abstract class InteractionResponseType {
  const PONG = 1;
  const CHANNEL_MESSAGE_WITH_SOURCE = 4;
  const DEFERRED_CHANNEL_MESSAGE_WITH_SOURCE = 5;
  const DEFERRED_UPDATE_MESSAGE = 6;
  const UPDATE_MESSAGE = 7;
  const APPLICATION_COMMAND_AUTOCOMPLETE_RESULT = 8;
  const MODAL = 9;
}

