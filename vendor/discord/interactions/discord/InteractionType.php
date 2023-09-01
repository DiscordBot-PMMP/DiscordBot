<?php

namespace Discord;

abstract class InteractionType {
  const PING = 1;
  const APPLICATION_COMMAND = 2;
  const MESSAGE_COMPONENT = 3;
  const APPLICATION_COMMAND_AUTOCOMPLETE = 4;
  const MODAL_SUBMIT = 5;
}

