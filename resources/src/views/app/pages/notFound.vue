<template>
  <b-row>
    <b-col>
      <div class="not-found-wrap text-center">
        <h1 class="text-60">
          404
        </h1>
        <p class="text-36 subheading mb-3">{{$t('Error')}}</p>
        <p class="mb-5  text-muted text-18">
          {{$t('page_not_exist')}}
        </p>
        <a class="btn btn-lg btn-primary btn-rounded" href="/"
          >{{$t('Go_back_to_home')}}</a
        >
      </div>
    </b-col>
  </b-row>
</template>
<script>
export default {
  metaInfo: {
    // if no subcomponents specify a metaInfo.title, this title will be used
    title: "404 Not Found"
  }
};
</script>

